<?php

namespace Kommercio\Http\Controllers\Frontend;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Kommercio\Events\CatalogQueryBuilder as CatalogQueryBuilderEvent;
use Kommercio\Facades\FrontendHelper;
use Kommercio\Facades\LanguageHelper;
use Kommercio\Facades\PriceFormatter;
use Kommercio\Facades\ProductIndexHelper;
use Kommercio\Facades\RuntimeCache;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\Manufacturer;
use Kommercio\Models\Product;
use Kommercio\Models\ProductAttribute\ProductAttributeValue;
use Kommercio\Models\ProductCategory;
use Kommercio\Models\ProductDetail;
use Kommercio\Models\ProductFeature\ProductFeatureValue;

class CatalogController extends Controller
{
    public function productCategories($parent_id = null)
    {
        $qb = ProductCategory::query();

        if($parent_id){
            $qb->where('parent_id', $parent_id);
        }else{
            $qb->whereNull('parent_id');
        }

        $event_results = Event::fire(new CatalogQueryBuilderEvent('product_categories', $qb));

        //If not processed, build default query here
        if(!isset($event_results[0]) || empty($event_results[0])){
            $qb->active()->orderBy('sort_order', 'ASC');
            $productCategories = $qb->get();
        }else{
            $productCategories = $event_results[0];
        }

        //Build template suggestions
        $suggestions = [];

        if($parent_id){
            $suggestions[] = 'frontend.catalog.product_category.index_'.$parent_id;
        }
        $suggestions[] = 'frontend.catalog.product_category.index';

        $view_name = ProjectHelper::findViewTemplate($suggestions);

        return view($view_name, [
            'productCategories' => $productCategories
        ]);
    }

    public function viewProduct(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $defaultVariation = null;
        //If variable, select first $variation
        if($product->combination_type == Product::COMBINATION_TYPE_VARIABLE){
            if($request->has('variation')){
                $defaultVariation = Product::findOrFail($request->get('variation'));
            }else{
                $defaultVariation = $product->getDefaultVariation();
            }
        }else{
            $defaultVariation = $product;
        }

        if(!$product->productDetail->active){
            return redirect()->back()->withErrors([trans(LanguageHelper::getTranslationKey('frontend.product.not_active'))]);
        }

        $view_name = ProjectHelper::findViewTemplate($product->getViewSuggestions());

        return view($view_name, [
            'product' => $product,
            'defaultVariation' => $defaultVariation,
            'seoModel' => $product
        ]);
    }

    public function search(Request $request, $view_name = null)
    {
        $instilledRequest = $this->instillCatalogRequest($request);
        $options = $instilledRequest['options'];
        $facetOptions = $instilledRequest['facetOptions'];

        $qb = Product::productEntity();

        $this->filterQuery($qb, $options, $facetOptions);
        $this->addSortQuery($qb, $options);

        if($options['new']){
            $qb->isNew();
        }

        $event_results = Event::fire(new CatalogQueryBuilderEvent('search', $qb, $request, $options));

        $total = $qb->count();

        $facetOptions['products'] = $qb->get();

        //If not processed, build default query here
        if(!isset($event_results[0]) || empty($event_results[0])){
            $products = $qb->paginate($options['limit']);
        }else{
            $products = $event_results[0];
        }

        $appendedOptions = $options;
        foreach($appendedOptions as $key => $appendedOption){
            if(!$request->has($key)){
                unset($appendedOptions[$key]);
            }
        }

        $products->setPath(FrontendHelper::get_url($request->path()))->appends($appendedOptions);

        $views = ['frontend.catalog.product.search'];

        if(empty($view_name)){
            $view_name = ProjectHelper::findViewTemplate($views);
        }

        $seoData = [
            'meta_title' => trans(LanguageHelper::getTranslationKey('frontend.seo.catalog.search.meta_title'), ['keyword' => $request->input('keyword')])
        ];

        $facetedNavigation = $this->getFacetedNavigation($facetOptions);

        return view($view_name, [
            'products' => $products,
            'total' => $total,
            'options' => $options,
            'facetOptions' => $facetOptions,
            'seoData' => $seoData,
            'facetedNavigation' => $facetedNavigation
        ]);
    }

    public function searchAutocomplete(Request $request)
    {
        $instilledRequest = $this->instillCatalogRequest($request);
        $options = $instilledRequest['options'];

        $qb = Product::productEntity();

        $qb->joinTranslation()->joinDetail()->selectSelf()
            ->where('D.active', true)
            ->whereIn('D.visibility', [ProductDetail::VISIBILITY_EVERYWHERE, ProductDetail::VISIBILITY_SEARCH]);

        if(!empty($options['keyword'])){
            $qb->where(function($qb) use ($options){
                $qb->where('T.name', 'LIKE', '%'.$options['keyword'].'%');

                $qb->orWhere('sku', 'LIKE', '%'.$options['keyword'].'%');

                $qb->orWhereHas('variations', function($query) use ($options){
                    $query->where('name', 'LIKE', '%'.$options['keyword'].'%')->orWhere('sku', 'LIKE', '%'.$options['keyword'].'%');
                });

                $qb->orWhereHas('categories', function($query) use ($options){
                    $query->whereTranslationLike('name', '%'.$options['keyword'].'%');
                });

                $qb->orWhereHas('manufacturer', function($query) use ($options){
                    $query->where('name', 'LIKE', '%'.$options['keyword'].'%');
                });
            });
        }

        $products = $qb->take($options['limit'])->get();

        $return = [];
        foreach($products as $product){
            $return[] = [
                'id' => $product->id,
                'name' => $product->name,
                'thumbnail' => $product->hasThumbnail()?asset($product->getThumbnail()->getImagePath('product_thumbnail')):null,
                'path' => $product->getExternalPath()
            ];
        }

        return new JsonResponse($return);
    }

    public function shop(Request $request)
    {
        $instilledRequest = $this->instillCatalogRequest($request);
        $options = $instilledRequest['options'];
        $facetOptions = $instilledRequest['facetOptions'];

        $qb = Product::productEntity();

        $this->filterQuery($qb, $options, $facetOptions);
        $this->addSortQuery($qb, $options);

        if($options['new']){
            $qb->isNew();
        }

        $event_results = Event::fire(new CatalogQueryBuilderEvent('products', $qb, $request, $options));

        $total = $qb->count();

        //If not processed, build default query here
        if(!isset($event_results[0]) || empty($event_results[0])){
            $products = $qb->paginate($options['limit']);
        }else{
            $products = $event_results[0];
        }

        $appendedOptions = $options;
        foreach($appendedOptions as $key => $appendedOption){
            if(!$request->has($key)){
                unset($appendedOptions[$key]);
            }
        }

        $products->setPath(FrontendHelper::getUrl($request->path()))->appends($appendedOptions);

        $views = ['frontend.catalog.shop'];

        if($options['new']){
            array_unshift($views, 'frontend.catalog.product.new');
        }

        $view_name = ProjectHelper::findViewTemplate($views);

        $seoData = [
            'meta_title' => trans(LanguageHelper::getTranslationKey('frontend.seo.catalog.shop.meta_title'))
        ];

        $facetedNavigation = $this->getFacetedNavigation($facetOptions);

        return view($view_name, [
            'products' => $products,
            'total' => $total,
            'options' => $options,
            'facetOptions' => $facetOptions,
            'seoData' => $seoData,
            'facetedNavigation' => $facetedNavigation
        ]);
    }

    public function newArrival(Request $request)
    {
        $attributes = $request->all();
        $attributes['new'] = TRUE;

        $request->replace($attributes);

        $view_name = ProjectHelper::findViewTemplate(['frontend.catalog.new_arrival']);

        $seoData = [
            'meta_title' => trans(LanguageHelper::getTranslationKey('frontend.seo.catalog.new_arrival.meta_title'))
        ];

        return $this->search($request, $view_name)->with('seoData', $seoData);
    }

    public function viewCategory(Request $request, $id)
    {
        $productCategory = ProductCategory::findOrFail($id);

        if(!$productCategory->active){
            return redirect()->back()->withErrors([trans(LanguageHelper::getTranslationKey('frontend.product_category.not_active'))]);
        }

        $instilledRequest = $this->instillCatalogRequest($request);
        $options = $instilledRequest['options'];
        $facetOptions = $instilledRequest['facetOptions'];

        $qb = $productCategory->products();
        $this->filterQuery($qb, $options, $facetOptions);
        $this->addSortQuery($qb, $options);

        $event_results = Event::fire(new CatalogQueryBuilderEvent('product_category_products', $qb, $request, $options));

        $total = $qb->count();

        $facetOptions['products'] = $qb->get();

        //If not processed, build default query here
        if(!isset($event_results[0]) || empty($event_results[0])){
            $products = $qb->paginate($options['limit']);
        }else{
            $products = $event_results[0];
        }

        $appendedOptions = $options;
        foreach($appendedOptions as $key => $appendedOption){
            if(!$request->has($key)){
                unset($appendedOptions[$key]);
            }
        }

        $products->setPath(FrontendHelper::getUrl($request->path()))->appends($appendedOptions);

        $view_name = ProjectHelper::findViewTemplate($productCategory->getViewSuggestions());

        $facetedNavigation = $this->getFacetedNavigation($facetOptions);

        return view($view_name, [
            'productCategory' => $productCategory,
            'products' => $products,
            'total' => $total,
            'options' => $options,
            'facetOptions' => $facetOptions,
            'seoModel' => $productCategory,
            'facetedNavigation' => $facetedNavigation
        ]);
    }

    protected function addSortQuery($qb, $options)
    {
        $qb->joinTranslation()->joinDetail()->selectSelf()
            ->where('D.active', true)
            ->whereIn('D.visibility', [ProductDetail::VISIBILITY_EVERYWHERE, ProductDetail::VISIBILITY_SEARCH]);

        switch($options['sort_by']){
            case 'newest':
                $qb->orderBy('products.created_at', $options['sort_dir']);
                break;
            case 'price':
                $qb->orderBy('D.retail_price', $options['sort_dir']);
                break;
            case 'name':
                $qb->orderBy('T.name', $options['sort_dir']);
                break;
            default:
                $qb->orderBy('D.sort_order', $options['sort_dir']);
                break;
        }

        if(isset($options['new']) && $options['new']){
            $qb->isNew();
        }
    }

    protected function instillCatalogRequest(Request $request)
    {
        $options = [
            'limit' => $request->input('limit', ProjectHelper::getConfig('catalog_options.limit')),
            'sort_by' => $request->input('sort_by', ProjectHelper::getConfig('catalog_options.sort_by')),
            'sort_dir' => $request->input('sort_dir', ProjectHelper::getConfig('catalog_options.sort_dir')),
            'new' => $request->input('new', false),
            'keyword' => $request->input('keyword', null),
            'page' => $request->input('page', null),
        ];

        $facetOptions = array_diff_key($request->all(), $options);

        // If facetOption is array, reconstruct it to be string
        foreach($facetOptions as $facetSlug => &$facetOption){
            if($facetSlug == 'categories'){
                foreach($facetOption as &$subFacetOption){
                    if(is_array($subFacetOption)){
                        $subFacetOption = implode('--', $subFacetOption);
                    }
                }
            }else{
                if(is_array($facetOption)){
                    $facetOption = implode('--', $facetOption);
                }
            }
        }

        return [
            'options' => $options,
            'facetOptions' => $facetOptions
        ];
    }

    protected function filterQuery($qb, $options = [], $facetOptions = [])
    {
        $qb->active()->catalogVisible();

        if(!empty($options['keyword'])){
            $qb->where(function($qb) use ($options){
                $qb->where('T.name', 'LIKE', '%'.$options['keyword'].'%');

                $qb->orWhere('sku', 'LIKE', '%'.$options['keyword'].'%');

                $qb->orWhereHas('variations', function($query) use ($options){
                    $query->where('name', 'LIKE', '%'.$options['keyword'].'%')->orWhere('sku', 'LIKE', '%'.$options['keyword'].'%');
                });

                $qb->orWhereHas('categories', function($query) use ($options){
                    $query->whereTranslationLike('name', '%'.$options['keyword'].'%');
                });

                $qb->orWhereHas('manufacturer', function($query) use ($options){
                    $query->where('name', 'LIKE', '%'.$options['keyword'].'%');
                });
            });
        }

        if(!empty($facetOptions['manufacturer'])){
            $manufacturers = [];
            foreach(explode('--', $facetOptions['manufacturer']) as $manufacturerSlug){
                $manufacturer = RuntimeCache::getOrSet('manufacturer['.$manufacturerSlug.']', function() use ($manufacturerSlug){
                    return Manufacturer::where('slug', $manufacturerSlug)->first();
                });

                if($manufacturer){
                    $manufacturers[] = $manufacturer->id;
                }else{
                    $manufacturers[] = 'wrong filter';
                }
            }

            $qb->whereIn('manufacturer_id', $manufacturers);
        }

        if(!empty($facetOptions['categories'])){
            foreach($facetOptions['categories'] as $categorySlug => $categoriesInString){
                if(empty($categoriesInString)){
                    continue;
                }

                $categories = [];
                foreach(explode('--', $categoriesInString) as $categorySlug){
                    $category = RuntimeCache::getOrSet('product_category['.$categorySlug.']', function() use ($categorySlug){
                        return ProductCategory::whereTranslation('slug', $categorySlug)->first();
                    });

                    if($category){
                        $categories[] = $category->id;
                    }else{
                        $categories[] = 'wrong filter';
                    }
                }

                $qb->whereHas('categories', function($query) use ($categories){
                    $query->whereIn('id', $categories);
                });
            }
        }

        if(!empty($facetOptions['category'])){
            $categories = [];
            foreach(explode('--', $facetOptions['category']) as $categorySlug){
                $category = RuntimeCache::getOrSet('product_category['.$categorySlug.']', function() use ($categorySlug){
                    return ProductCategory::whereTranslation('slug', $categorySlug)->first();
                });

                if($category){
                    $categories[] = $category->id;
                }else{
                    $categories[] = 'wrong filter';
                }
            }

            $qb->whereHas('categories', function($query) use ($categories){
                $query->whereIn('id', $categories);
            });
        }

        if(!empty($facetOptions['category'])){
            $categories = [];
            foreach(explode('--', $facetOptions['category']) as $categorySlug){
                $category = RuntimeCache::getOrSet('product_category['.$categorySlug.']', function() use ($categorySlug){
                    return ProductCategory::whereTranslation('slug', $categorySlug)->first();
                });

                if($category){
                    $categories[] = $category->id;
                }else{
                    $categories[] = 'wrong filter';
                }
            }

            $qb->whereHas('categories', function($query) use ($categories){
                $query->whereIn('id', $categories);
            });
        }

        if(!empty($facetOptions['attribute'])){
            foreach($facetOptions['attribute'] as $attribute => $attributeParameter){
                $attributeValues = [];

                foreach(explode('--', $attributeParameter) as $attributeValue){
                    $attributeValue = RuntimeCache::getOrSet('product_attribute_value['.$attributeValue.']', function() use ($attributeValue){
                        return ProductAttributeValue::whereTranslation('slug', $attributeValue)->first();
                    });

                    if($attributeValue){
                        $attributeValues[] = $attributeValue->id;
                    }else{
                        $attributeValues[] = 'wrong filter';
                    }
                }

                if(count($attributeValues) > 0){
                    $qb->where(function($query) use ($attributeValues){
                        $query->whereHas('productAttributeValues', function($query) use ($attributeValues){
                            $query->whereIn('id', $attributeValues);
                        });

                        $query->orWhereHas('variations', function($query) use ($attributeValues){
                            $query->whereHas('productAttributeValues', function($query) use ($attributeValues){
                                $query->whereIn('id', $attributeValues);
                            });
                        });
                    });
                }
            }
        }

        if(!empty($facetOptions['feature'])){
            foreach($facetOptions['feature'] as $feature => $featureParameter){
                $featureValues = [];

                foreach(explode('--', $featureParameter) as $featureValue){
                    $featureValue = RuntimeCache::getOrSet('product_feature_value['.$featureValue.']', function() use ($featureValue){
                        return ProductFeatureValue::whereTranslation('slug', $featureValue)->first();
                    });

                    if($featureValue){
                        $featureValues[] = $featureValue->id;
                    }else{
                        $featureValues[] = 'wrong filter';
                    }
                }
            }

            if(count($featureValues) > 0){
                $qb->where(function($query) use ($featureValues){
                    $query->whereHas('productFeatureValues', function($query) use ($featureValues){
                        $query->whereIn('id', $featureValues);
                    });

                    $query->orWhereHas('variations', function($query) use ($featureValues){
                        $query->whereHas('productFeatureValues', function($query) use ($featureValues){
                            $query->whereIn('id', $featureValues);
                        });
                    });
                });
            }
        }
    }

    protected function getFacetedNavigation($options = [])
    {
        $facetedLayers = [];

        if(ProjectHelper::isFeatureEnabled('catalog.faceted_navigation')){
            $facetedLayers = ProductIndexHelper::buildFacetedNavigation($options);
        }

        return $facetedLayers;
    }
}
