<?php

namespace Kommercio\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Kommercio\Events\CatalogQueryBuilder as CatalogQueryBuilderEvent;
use Kommercio\Facades\FrontendHelper;
use Kommercio\Facades\LanguageHelper;
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

    public function viewProduct($id)
    {
        $product = Product::findOrFail($id);

        $defaultVariation = null;
        //If variable, select first $variation
        if($product->combination_type = Product::COMBINATION_TYPE_VARIABLE){
            $defaultVariation = $product->getDefaultVariation();
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

    public function viewCategory(Request $request, $id)
    {
        $productCategory = ProductCategory::findOrFail($id);

        if(!$productCategory->active){
            return redirect()->back()->withErrors([trans(LanguageHelper::getTranslationKey('frontend.product_category.not_active'))]);
        }

        $options = [
            'limit' => $request->input('limit', ProjectHelper::getConfig('catalog.limit')),
            'sort_by' => $request->input('sort_by', ProjectHelper::getConfig('catalog.sort_by')),
            'sort_dir' => $request->input('sort_dir', ProjectHelper::getConfig('catalog.sort_dir'))
        ];

        $qb = $productCategory->products();

        $event_results = Event::fire(new CatalogQueryBuilderEvent('product_category_products', $qb, $request, $options));

        //If not processed, build default query here
        if(!isset($event_results[0]) || empty($event_results[0])){
            $qb->joinDetail()->selectSelf()
                ->where('active', true)
                ->whereIn('visibility', [ProductDetail::VISIBILITY_EVERYWHERE, ProductDetail::VISIBILITY_CATALOG])
                ->orderBy('sort_order', 'ASC');
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
