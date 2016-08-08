<?php

namespace Kommercio\Http\Controllers\Frontend;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Kommercio\Events\CatalogQueryBuilder as CatalogQueryBuilderEvent;
use Kommercio\Facades\FrontendHelper;
use Kommercio\Facades\LanguageHelper;
use Kommercio\Facades\PriceFormatter;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\Product;
use Kommercio\Models\ProductCategory;
use Kommercio\Models\ProductDetail;

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

    public function search(Request $request)
    {
        $options = [
            'limit' => $request->input('limit', ProjectHelper::getConfig('catalog_options.limit')),
            'sort_by' => $request->input('sort_by', ProjectHelper::getConfig('catalog_options.sort_by')),
            'sort_dir' => $request->input('sort_dir', ProjectHelper::getConfig('catalog_options.sort_dir')),
            'keyword' => $request->input('keyword'),
            'new' => $request->input('new', false)
        ];

        $qb = Product::productEntity();

        $event_results = Event::fire(new CatalogQueryBuilderEvent('products', $qb, $request, $options));

        //If not processed, build default query here
        if(!isset($event_results[0]) || empty($event_results[0])){
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

            if($options['new']){
                $qb->isNew();
            }

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

        if($options['new']){
            array_unshift($views, 'frontend.catalog.product.new');
        }

        $view_name = ProjectHelper::findViewTemplate($views);

        return view($view_name, [
            'products' => $products,
            'options' => $options,
        ]);
    }

    public function searchAutocomplete(Request $request)
    {
        $options = [
            'limit' => $request->input('limit', 5),
            'keyword' => $request->input('keyword'),
        ];

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

    public function newArrival(Request $request)
    {
        $attributes = $request->all();
        $attributes['new'] = TRUE;

        $request->replace($attributes);

        return $this->search($request);
    }

    public function viewCategory(Request $request, $id)
    {
        $productCategory = ProductCategory::findOrFail($id);

        if(!$productCategory->active){
            return redirect()->back()->withErrors([trans(LanguageHelper::getTranslationKey('frontend.product_category.not_active'))]);
        }

        $options = [
            'limit' => $request->input('limit', ProjectHelper::getConfig('catalog_options.limit')),
            'sort_by' => $request->input('sort_by', ProjectHelper::getConfig('catalog_options.sort_by')),
            'sort_dir' => $request->input('sort_dir', ProjectHelper::getConfig('catalog_options.sort_dir'))
        ];

        $qb = $productCategory->products();

        $event_results = Event::fire(new CatalogQueryBuilderEvent('product_category_products', $qb, $request, $options));

        //If not processed, build default query here
        if(!isset($event_results[0]) || empty($event_results[0])){
            $qb->joinTranslation()->joinDetail()->selectSelf()
                ->where('D.active', true)
                ->whereIn('D.visibility', [ProductDetail::VISIBILITY_EVERYWHERE, ProductDetail::VISIBILITY_CATALOG]);

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

        $view_name = ProjectHelper::findViewTemplate($productCategory->getViewSuggestions());

        return view($view_name, [
            'productCategory' => $productCategory,
            'products' => $products,
            'options' => $options,
            'seoModel' => $productCategory
        ]);
    }
}
