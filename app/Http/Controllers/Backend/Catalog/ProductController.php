<?php

namespace Kommercio\Http\Controllers\Backend\Catalog;

use Carbon\Carbon;
use Collective\Html\FormFacade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as RequestFacade;
use Illuminate\Support\Facades\Session;
use Kommercio\Facades\CurrencyHelper;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Http\Requests\Backend\Catalog\ProductFormRequest;
use Kommercio\Http\Requests\Backend\Catalog\ProductVariationFormRequest;
use Kommercio\Models\Order\Order;
use Kommercio\Models\Order\OrderLimit;
use Kommercio\Models\Product;
use Kommercio\Models\ProductAttribute\ProductAttribute;
use Kommercio\Models\ProductAttribute\ProductAttributeValue;
use Kommercio\Models\ProductCategory;
use Kommercio\Models\ProductDetail;
use Kommercio\Models\ProductFeature\ProductFeature;
use Kommercio\Models\ProductFeature\ProductFeatureValue;
use Kommercio\Facades\PriceFormatter;

class ProductController extends Controller{
    public function index(Request $request)
    {
        $qb = Product::productEntity();

        if($request->ajax() || $request->wantsJson()){
            $totalRecords = $qb->count();

            //Join Translation and Detail
            $qb->with('defaultCategory', 'manufacturer')->joinTranslation()->joinDetail()->selectSelf();

            foreach($request->input('filter', []) as $searchKey=>$search){
                if(trim($search) != ''){
                    if($searchKey == 'category_name'){
                        $qb->whereHas('categories', function($query) use ($search){
                            $query->whereTranslationLike('name', '%'.$search.'%');
                        });
                    }elseif($searchKey == 'manufacturer') {
                        $qb->whereHas('manufacturer', function($query) use ($search){
                            $query->where('name', 'LIKE', '%'.$search.'%');
                        });
                    }else{
                        $qb->where($searchKey, 'LIKE', '%'.$search.'%');
                    }
                }
            }

            $filteredRecords = $qb->count();

            $columns = $request->input('columns');
            foreach($request->input('order', []) as $order){
                $orderColumn = $columns[$order['column']];

                $qb->orderBy($orderColumn['name'], $order['dir']);
            }

            if($request->has('length')){
                $qb->take($request->input('length'));
            }

            if($request->has('start') && $request->input('start') > 0){
                $qb->skip($request->input('start'));
            }

            $products = $qb->get();

            $meat = $this->prepareDatatables($products, $request->input('start'));

            $data = [
                'draw' => $request->input('draw'),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $meat
            ];

            return response()->json($data);
        }

        return view('backend.catalog.product.index');
    }

    protected function prepareDatatables($products, $orderingStart=0)
    {
        $meat= [];

        foreach($products as $idx=>$product){
            $productAction = FormFacade::open(['route' => ['backend.catalog.product.delete', 'id' => $product->id]]);
            $productAction .= '<div class="btn-group btn-group-sm">';
            $productAction .= '<a class="btn btn-default" href="'.route('backend.catalog.product.edit', ['id' => $product->id, 'backUrl' => RequestFacade::fullUrl()]).'"><i class="fa fa-pencil"></i> Edit</a>';
            $productAction .= '<button class="btn btn-default" data-toggle="confirmation" data-original-title="Are you sure?" title=""><i class="fa fa-trash-o"></i> Delete</button></div>';
            $productAction .= FormFacade::close();

            $meat[] = [
                $idx + 1 + $orderingStart,
                $product->hasThumbnail()?'<img class="img-responsive" src="'.asset($product->thumbnail->getImagePath('backend_thumbnail')).'" />':'',
                $product->name,
                $product->sku,
                $product->defaultCategory?$product->defaultCategory->name:'',
                $product->manufacturer?$product->manufacturer->name:'',
                PriceFormatter::formatNumber($product->getRetailPrice()),
                PriceFormatter::formatNumber($product->getNetPrice()),
                '<i class="fa fa-'.(isset($product->productDetail) && $product->productDetail->active?'check text-success':'remove text-danger').'"></i>',
                $product->created_at->format('d M Y H:i'),
                $productAction
            ];
        }

        return $meat;
    }

    public function create()
    {
        $product = new Product();

        $currencyOptions = CurrencyHelper::getCurrencyOptions();

        return view('backend.catalog.product.create', [
            'product' => $product,
            'currencyOptions' => $currencyOptions
        ]);
    }

    public function store(ProductFormRequest $request)
    {
        $product = new Product($request->all());
        $product->combination_type = $request->input('combination_type');
        if($request->has('default_category')){
            $category = ProductCategory::findOrFail($request->input('default_category'));
            $product->defaultCategory()->associate($category);
        }

        $product->save();

        $product->categories()->sync($request->input('categories', []));

        $images = [];
        foreach($request->input('images', []) as $idx=>$image){
            $images[$image] = [
                'type' => 'image',
                'caption' => $request->input('images_caption.'.$idx, null),
                'locale' => $product->getTranslation()->locale
            ];
        }
        $product->getTranslation()->syncMedia($images, 'image');

        $thumbnails = [];
        foreach($request->input('thumbnails', []) as $idx=>$image){
            $thumbnails[$image] = [
                'type' => 'thumbnail',
                'caption' => $request->input('thumbnails_caption.'.$idx, null),
                'locale' => $product->getTranslation()->locale
            ];
        }
        $product->getTranslation()->syncMedia($thumbnails, 'thumbnail');

        $productDetail = new ProductDetail();
        $productDetail->fill($request->input('productDetail'));
        $productDetail->store_id = $request->input('store_id');
        $productDetail->product()->associate($product);
        $productDetail->save();

        $message = 'New product '.$product->name.' is successfully created.';

        if($request->get('action') == 'save_stay'){
            return redirect()->route('backend.catalog.product.edit', ['id' => $product->id])->with('success', [$message]);
        }else{
            return redirect($request->get('backUrl', route('backend.catalog.product.index')))->with('success', [$message]);
        }
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);

        $oldFeatures = array_keys(old('features', []));

        $features = [];
        if($oldFeatures){
            foreach($oldFeatures as $oldFeature){
                $features[] = ProductFeature::findOrFail($oldFeature);
            }
        }else{
            $features = $product->productFeatures;
            $oldFeatures = $features->pluck('id')->all();
        }

        $allFeatures = ProductFeature::withTranslation()->get();

        $featureOptions = [];
        foreach($allFeatures as $allFeature){
            if(!in_array($allFeature->id, $oldFeatures)){
                $featureOptions[$allFeature->id] = $allFeature->name;
            }
        }

        $currencyOptions = CurrencyHelper::getCurrencyOptions();

        return view('backend.catalog.product.edit', [
            'product' => $product,
            'featureOptions' => $featureOptions,
            'features' => $features,
            'currencyOptions' => $currencyOptions
        ]);
    }

    public function update(ProductFormRequest $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->fill($request->all());

        if($request->has('default_category')){
            $category = ProductCategory::findOrFail($request->input('default_category'));
            $product->defaultCategory()->associate($category);
        }

        $product->save();

        $product->categories()->sync($request->input('categories', []));

        $images = [];
        foreach($request->input('images', []) as $idx=>$image){
            $images[$image] = [
                'type' => 'image',
                'caption' => $request->input('images_caption.'.$idx, null),
                'locale' => $product->getTranslation()->locale
            ];
        }
        $product->getTranslation()->syncMedia($images, 'image');

        $thumbnails = [];
        foreach($request->input('thumbnails', []) as $idx=>$image){
            $thumbnails[$image] = [
                'type' => 'thumbnail',
                'caption' => $request->input('thumbnails_caption.'.$idx, null),
                'locale' => $product->getTranslation()->locale
            ];
        }
        $product->getTranslation()->syncMedia($thumbnails, 'thumbnail');

        $productDetail = $product->productDetail;

        if(!$productDetail){
            $productDetail = new ProductDetail();
            $productDetail->product()->associate($product);
        }

        $productDetail->fill($request->input('productDetail'));
        $productDetail->store_id = $request->input('store_id');

        $productDetail->save();

        //Update Product Features and save custom feature value
        $syncFeatures = [];
        foreach($request->input('features', []) as $featureId=>$featureValue){
            if($request->has('features_custom.'.$featureId)){
                $newFeatureValue = ProductFeatureValue::whereTranslation('name', $request->input('features_custom.'.$featureId))->first();

                if(!$newFeatureValue){
                    $newFeatureValue = new ProductFeatureValue();
                    $newFeatureValue->fill([
                        'name' => $request->input('features_custom.'.$featureId),
                        'custom' => TRUE,
                    ]);

                    $newFeatureValue->productFeature()->associate($featureId);
                    $newFeatureValue->save();
                }

                $featureValue = $newFeatureValue->id;
            }

            if(!empty($featureValue)){
                $syncFeatures[$featureValue] = [
                    'product_feature_id' => $featureId
                ];
            }
        }
        $product->productFeatureValues()->sync($syncFeatures);

        //Inventory
        if($product->variations->count() > 0){
            foreach($product->variations as $variation){
                $variation->productDetail->manage_stock = $request->input('variation.'.$variation->id.'.productDetail.manage_stock', false);
                $variation->productDetail->taxable = $productDetail->taxable;
                $variation->productDetail->save();

                //Update children product categories
                $variation->categories()->sync($request->input('categories', []));

                //Update children features
                $variation->productFeatureValues()->sync($syncFeatures);

                //Update manufacturer
                $variation->manufacturer_id = $request->input('manufacturer_id', null);

                if($variation->productDetail->manage_stock){
                    $variation->saveStock($request->input('variation.'.$variation->id.'.stock'), $request->input('warehouse_id'));
                }

                $variation->save();
            }
        }else{
            if($productDetail->manage_stock){
                $product->saveStock($request->input('stock'), $request->input('warehouse_id'));
            }
        }

        $message = $product->name.' has successfully been updated.';

        if($request->get('action') == 'save_stay'){
            return redirect()->route('backend.catalog.product.edit', ['id' => $product->id])->with('success', [$message]);
        }else{
            return redirect($request->get('backUrl', route('backend.catalog.product.index')))->with('success', [$message]);
        }
    }

    public function delete(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        if(!$this->deleteable($product->id)){
            return redirect()->back()->withErrors(['Can\'t delete this product. It is used in settled Orders.']);
        }

        $name = $product->name;

        //Remove all media first. We do it manually because Translation model is cascaded, so we can't do this on Translation delete
        foreach($product->translations as $translation){
            $translation->deleteMedia('image');
            $translation->deleteMedia('thumbnail');
        }

        $product->forceDelete();

        if($request->ajax()){
            return response()->json([
                'result' => 'success',
                'message' => $name.' has been deleted.',
                '_token' => csrf_token()
            ]);
        }else{
            return redirect()->back()->with('success', [$name.' has been deleted.']);
        }
    }

    public function variationIndex($id)
    {
        $parentProduct = Product::findOrFail($id);

        $return = view('backend.catalog.product.product_variation_index', [
            'variations' => $parentProduct->variations,
            'product' => $parentProduct
        ])->render();

        return response()->json([
            'html' => $return,
            '_token' => csrf_token()
        ]);
    }

    public function variationForm(Request $request, $id, $variation_id=null)
    {
        $product = Product::findOrFail($id);
        $variation = null;

        if($variation_id){
            $variation = Product::with('productAttributes', 'productDetail')->findOrFail($variation_id);

            $variationAttributes = $variation->getProductAttributeWithValues();
        }

        $existingAttributes = [];

        $notIns = $request->input('variation.attributes', isset($variationAttributes)?$variationAttributes:[]);

        if($request->has('variation.remove_attribute') && isset($notIns[$request->input('variation.remove_attribute')])){
            unset($notIns[$request->input('variation.remove_attribute')]);
        }

        $notIns = array_keys($notIns);

        if($request->has('variation.new_attribute') && !$request->has('variation.remove_attribute')){
            $notIns += $request->input('variation.new_attribute', []);
        }

        foreach($notIns as $notIn){
            $existingAttribute = ProductAttribute::findOrFail($notIn);
            $existingAttributes[$existingAttribute->id] = [
                'name' => $existingAttribute->name,
                'options' => $existingAttribute->values->pluck('name', 'id')->all()
            ];
        }

        $attributeOptions = [
            '' => 'Select attribute to add'
        ];

        $attributeQb = ProductAttribute::withTranslation()->orderBy('sort_order', 'ASC');

        if(!empty($notIns)){
            $attributeQb->whereNotIn('id', $notIns);
        }

        $attributes = $attributeQb->get();
        foreach($attributes as $attribute){
            if($attribute->values->count() > 0){
                $attributeOptions[$attribute->id] = $attribute->name;
            }
        }

        if($request->has('variation')){
            $oldValues = $request->all();
        }else{
            if($variation){
                $oldValues['variation'] = $variation->toArray();
                $oldValues['variation']['productDetail'] = $variation->productDetail->toArray();
                $oldValues['variation']['attributes'] = $variation->getProductAttributeWithValues();
            }else{
                $oldValues['variation'] = [
                    'sku' => $product->sku,
                    'productDetail' => [
                        //'retail_price' => $product->productDetail->retail_price,
                        'active' => true,
                        'available' => true,
                    ]
                ];
            }
        }

        Session::flashInput($oldValues);

        $form = view('backend.catalog.product.product_variation_form', [
            'product' => $product,
            'variation' => $variation,
            'attributeOptions' => $attributeOptions,
            'existingAttributes' => $existingAttributes
        ])->render();

        return response()->json([
            'html' => $form,
            '_token' => csrf_token()
        ]);
    }

    public function variationSave(ProductVariationFormRequest $request, $id, $variation_id=null)
    {
        $parentProduct = Product::findOrFail($id);

        $new = FALSE;
        $product = null;

        if($variation_id){
            $product = Product::findOrFail($variation_id);
        }

        if(!$product){
            $product = new Product();
            $product->combination_type = Product::COMBINATION_TYPE_VARIATION;

            $new = TRUE;

        }

        $product->fill($request->input('variation'));
        $product->name = $parentProduct->name;

        $loadedAttributeValues = [];
        $toSyncAttributeValues = [];

        foreach($request->input('variation.attributes', []) as $attributeId => $attributeValue){
            $loadedAttributeValues[$attributeId] = ProductAttributeValue::findOrFail($attributeValue);
            $product->name .= ' '.$loadedAttributeValues[$attributeId]->name;

            $toSyncAttributeValues[$attributeValue] = [
                'product_attribute_id' => $attributeId
            ];
        }

        $product->parent()->associate($parentProduct);

        //Update manufacturer
        $product->manufacturer_id = $parentProduct->manufacturer_id;

        $product->save();

        //Update children product categories
        $product->categories()->sync($parentProduct->categories->pluck('id')->all());

        //Update children features
        $features = [];

        foreach($parentProduct->productFeatureValues as $parentProductFeature){
            $features[$parentProductFeature->id] = [
                'product_feature_id' => $parentProductFeature->pivot->product_feature_id
            ];
        }

        $product->productFeatureValues()->sync($features);

        $product->productAttributeValues()->sync($toSyncAttributeValues);

        $images = [];

        if($request->has('variation.images')){
            foreach($request->input('variation.images', []) as $idx=>$image){
                $images[$image] = [
                    'type' => 'image',
                    'locale' => $product->getTranslation()->locale
                ];
            }
        }

        $product->getTranslation()->syncMedia($images, 'image');

        $thumbnails = [];

        if($request->has('variation.thumbnails')){
            foreach($request->input('variation.thumbnails', []) as $idx=>$image){
                $thumbnails[$image] = [
                    'type' => 'thumbnail',
                    'locale' => $product->getTranslation()->locale
                ];
            }
        }

        $product->getTranslation()->syncMedia($thumbnails, 'thumbnail');

        $productDetail = $product->productDetail;

        if(!$productDetail){
            $productDetail = new ProductDetail();
            $productDetail->product()->associate($product);
        }

        $productDetail->fill($request->input('variation.productDetail'));
        $productDetail->store_id = $request->input('variation.store_id');
        $productDetail->taxable = $parentProduct->productDetail->taxable;
        $productDetail->save();

        if($new){
            $message = $product->name.' is successfully created.';
        }else{
            $message = $product->name.' is successfully updated.';
        }

        return response()->json([
            'message' => $message,
            '_token' => csrf_token(),
            'result' => 'success'
        ]);
    }

    public function featureIndex(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $newFeatures = $request->input('new_features', []);

        $selectedFeatures = $request->input('features', []);

        if($request->has('remove_feature') && isset($selectedFeatures[$request->input('remove_feature')])){
            unset($selectedFeatures[$request->input('remove_feature')]);
        }else{
            $selectedFeatures += array_flip($newFeatures);
        }

        $allFeatures = ProductFeature::withTranslation()->get();

        $featureOptions = [];
        foreach($allFeatures as $allFeature){
            if(!isset($selectedFeatures[$allFeature->id])){
                $featureOptions[$allFeature->id] = $allFeature->name;
            }
        }

        $features = [];
        foreach($selectedFeatures as $selectedFeature => $selectedFeatureValue){
            $features[] = ProductFeature::findOrFail($selectedFeature);
        }

        Session::flashInput([
            'features' => $selectedFeatures
        ]);

        $return = view('backend.catalog.product.product_feature_index', [
            'product' => $product,
            'featureOptions' => $featureOptions,
            'features' => $features
        ])->render();

        return response()->json([
            'html' => $return
        ]);
    }

    public function autocomplete(Request $request)
    {
        $return = [];
        $search = $request->get('query', '');

        if(!empty($search)){
            $qb = Product::productSelection()->joinTranslation()->joinDetail()->selectSelf();

            $qb->where(function($query) use ($search){
                $query->where('name', 'LIKE', '%'.$search.'%')
                    ->orWhere('sku', 'LIKE', '%'.$search.'%');
            });

            $results = $qb->get();

            foreach($results as $result){
                $return[] = [
                    'id' => $result->id,
                    'name' => $result->name.' ('.$result->sku.')',
                    'sku' => $result->sku,
                    'thumbnail' => $result->hasThumbnail()?asset($result->thumbnail->getImagePath('backend_thumbnail')):'',
                    'tokens' => [
                        $result->name,
                        $result->sku
                    ]
                ];
            }
        }

        return response()->json(['data' => $return, '_token' => csrf_token()]);
    }

    protected function deleteable($id)
    {
        $orderCount = Order::checkout()->whereHasLineItem($id, 'product')->count();
        dd($orderCount);

        return $orderCount < 1;
    }
}