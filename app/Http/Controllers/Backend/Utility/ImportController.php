<?php

namespace Kommercio\Http\Controllers\Backend\Utility;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Http\Requests;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\Manufacturer;
use Kommercio\Models\Product;
use Kommercio\Models\ProductAttribute\ProductAttribute;
use Kommercio\Models\ProductAttribute\ProductAttributeValue;
use Kommercio\Models\ProductCategory;
use Kommercio\Models\ProductDetail;
use Kommercio\Utility\Import\Batch;

class ImportController extends Controller
{
    public function manufacturer(Request $request)
    {
        $return = $this->processBatch($request, [], function($result){
            $manufacturer = Manufacturer::where('name', $result->name)->first();

            if(!$manufacturer){
                $manufacturer = new Manufacturer();
            }

            $manufacturer->name = $result->name;
            $manufacturer->save();

            $newMedia = [];

            if($result->image){
                $downloadedImage = \Kommercio\Models\File::downloadFromUrl($result->image);

                if($downloadedImage){
                    $newMedia[$downloadedImage->id] = [
                        'type' => 'logo'
                    ];
                }
            }

            $manufacturer->syncMedia($newMedia, 'logo');
        });

        return $this->processResponse('backend.utility.import.form.manufacturer', $return, $request);
    }

    public function productAttribute(Request $request)
    {
        $return = $this->processBatch($request, ['import.product_attribute' => 'required|exists:product_attributes,id'], function($result){
            $productAttribute = ProductAttribute::findOrFail(Session::get('import.product_attribute'));

            $productAttributeValue = ProductAttributeValue::whereTranslation('name', $result->name)->first();

            if(!$productAttributeValue){
                $productAttributeValue = new ProductAttributeValue();
                $productAttributeValue->productAttribute()->associate($productAttribute);
            }

            $productAttributeValue->name = $result->name;
            $productAttributeValue->save();

            $newMedia = [];

            if($result->image){
                $downloadedImage = \Kommercio\Models\File::downloadFromUrl($result->image);

                if($downloadedImage){
                    $newMedia[$downloadedImage->id] = [
                        'type' => 'thumbnail'
                    ];
                }
            }

            $productAttributeValue->syncMedia($newMedia, 'thumbnail');
        });

        return $this->processResponse('backend.utility.import.form.product_attribute', $return, $request, function(){
            $productAttributes = ProductAttribute::orderBy('sort_order', 'ASC')->get();
            $productAttributeOptions = [];

            foreach($productAttributes as $productAttribute){
                $productAttributeOptions[$productAttribute->id] = $productAttribute->name;
            }

            return [
                'productAttributeOptions' => $productAttributeOptions
            ];
        });
    }

    public function product(Request $request)
    {
        $return = $this->processBatch($request, [], function($result){
            $product = Product::where('sku', $result->sku)->first();

            $manufacturer = null;

            if(!empty($result->manufacturer)){
                $manufacturer = Manufacturer::where('name', $result->manufacturer)->first();

                if(!$manufacturer){
                    return 'Manufacturer "'.$result->manufacturer.'" not found';
                }
            }

            $productCategories = [];
            if(!empty($result->product_category)){
                $categories = explode(';', $result->product_category);
                foreach($categories as $category){
                    $productCategory = ProductCategory::whereTranslation('name', $category)->first();

                    if(!$productCategory){
                        return 'Product Category "'.$category.'" not found';
                    }

                    $productCategories[] = $productCategory;
                }
            }

            //Attributes
            $productAttributeValues = [];

            foreach($result->all() as $key => $value){
                if(preg_match('/attribute/', $key)){
                    $attributeSlug = str_replace('attribute', '', $key);

                    $productAttribute = ProductAttribute::whereTranslation('slug', $attributeSlug)->first();

                    if(!$productAttribute){
                        return 'Product Attribute "'.$attributeSlug.'" not found';
                    }

                    $attributeValues = explode(';', $value);
                    foreach($attributeValues as $attributeValue){
                        $productAttributeValue = ProductAttributeValue::whereTranslation('name', $attributeValue)->first();

                        if(!$productAttributeValue){
                            return 'Product Attribute Value "'.$attributeValue.'" not found';
                        }

                        $productAttributeValues[] = $productAttributeValue;
                    }
                }
            }

            if(!$product){
                $product = new Product();
            }

            $product->name = $result->name;
            $product->sku = $result->sku;

            if($result->parent_sku == $result->sku){
                if(!$product->exists){
                    $product->combination_type = Product::COMBINATION_TYPE_SINGLE;
                }
            }else{
                $product->combination_type = Product::COMBINATION_TYPE_VARIATION;

                $parentProduct = Product::where('sku', $result->parent_sku)->first();
                if(!$parentProduct){
                    return 'Parent Product "'.$result->parent_sku.'" not found';
                }

                $parentProduct->update([
                    'combination_type' => Product::COMBINATION_TYPE_VARIABLE
                ]);
            }

            $product->save();
            $productDetail = new ProductDetail([
                'new' => (!empty($result->new) && $result->new),
                'active' => (!empty($result->active) && $result->active),
                'retail_price' => floatval($result->price),
                'weight' => $result->weight?:null,
                'manage_stock' => (!empty($result->manage_stock) && $result->manage_stock),
            ]);

            $store = ProjectHelper::getDefaultStore();
            $productDetail->store()->associate($store);

            $product->productDetails()->save($productDetail);

            if($result->manage_stock){
                $product->saveStock(floatval($result->stock));
            }

            if($manufacturer){
                $product->manufacturer()->associate($manufacturer);
            }

            if($productCategories){
                $productCategoryIds = [];
                foreach($productCategories as $productCategory){
                    $productCategoryIds[] = $productCategory->id;
                }

                $product->categories()->sync($productCategoryIds);
                $product->defaultCategory()->associate($productCategories[0]);
            }

            if($productAttributeValues){
                $productAttributeIds = [];
                foreach($productAttributeValues as $productAttributeValue){
                    $productAttributeIds[$productAttributeValue->id] = [
                        'product_attribute_id' => $productAttributeValue->product_attribute_id
                    ];
                }

                $product->productAttributeValues()->sync($productAttributeIds);
            }

            $product->save();

            $newMedia = [];
            if($result->images){
                $images = explode(';', $result->images);

                foreach($images as $image){
                    $downloadedImage = \Kommercio\Models\File::downloadFromUrl($image);

                    if($downloadedImage){
                        $newMedia[$downloadedImage->id] = [
                            'type' => 'image',
                            'locale' => $product->getTranslation()->locale
                        ];
                    }
                }
            }

            $product->getTranslation()->syncMedia($newMedia, 'image');

            $newThumbnail = [];
            if($result->images){
                $images = explode(';', $result->images);

                foreach($images as $image){
                    $downloadedImage = \Kommercio\Models\File::downloadFromUrl($image);

                    if($downloadedImage){
                        $newThumbnail[$downloadedImage->id] = [
                            'type' => 'thumbnail',
                            'locale' => $product->getTranslation()->locale
                        ];

                        break;
                    }
                }
            }

            $product->getTranslation()->syncMedia($newThumbnail, 'thumbnail');
        });

        return $this->processResponse('backend.utility.import.form.product', $return, $request);
    }

    protected function processBatch(Request $request, $additionalRules = [], \Closure $closure)
    {
        $routeName = $request->route()->getName();

        if($request->isMethod('POST')){
            $rules = [
                'file' => 'required|mimes:xlsx,xls'
            ];

            $rules = array_merge($rules, $additionalRules);

            $this->validate($request, $rules);

            $file = $request->file('file');

            $batch = Batch::init($file);

            Session::put('import', $request->input('import', []));
            Session::flashInput(['import' => Session::get('import')]);

            return [
                'url' => route($routeName, ['run' => 1, 'batch_id' => $batch->id, 'row' => 0]),
                'row' => null
            ];
        }else{
            if($request->has('run')){
                $rules = [
                    'batch_id' => 'required|integer|exists:import_batches,id',
                    'row' => 'required|integer'
                ];

                $validator = Validator::make($request->all(), $rules);

                if ($validator->fails()) {
                    $errors = $validator->errors()->getMessages();

                    return redirect()->back()->withErrors($errors);
                }

                $batch = Batch::findOrFail($request->input('batch_id'));

                if($batch->hasRow($request->input('row'))){
                    $importItem = $batch->process($request->input('row'), $closure);

                    return [
                        'url' => route($routeName, ['run' => 1, 'batch_id' => $batch->id, 'row' => $request->input('row') + 1]),
                        'row' => $importItem
                    ];
                }else{
                    $batch->clean();

                    Session::flashInput(['import' => Session::get('import')]);
                    Session::forget('import');

                    return redirect()->route($routeName, ['success' => 1, 'batch_id' => $batch->id])->with('success', ['File is successfully imported']);
                }
            }
        }
    }

    protected function processResponse($view_name, $return, Request $request, \Closure $getAdditionalViewOptions = null)
    {
        if($request->ajax()){
            if($return instanceof RedirectResponse){
                $json = [
                    'nextUrl' => null,
                    'reload' => $return->getTargetUrl(),
                    'row' => null
                ];
            }else{
                $json = [
                    'nextUrl' => $return['url'],
                    'reload' => null,
                    'row' => $return['row']
                ];
            }

            return new JsonResponse($json);
        }

        if($return instanceof RedirectResponse){
            return $return;
        }else{
            $runUrl = $return['url'];
        }

        if($request->has('success') && $request->has('batch_id')){
            $batch = Batch::findOrFail($request->input('batch_id'));
            $rows = $batch->items;
        }else{
            $rows = collect([]);
        }

        $viewOptions = $getAdditionalViewOptions?$getAdditionalViewOptions():[];

        return view($view_name, array_merge([
            'runUrl' => $runUrl,
            'rows' => $rows
        ], $viewOptions));
    }
}
