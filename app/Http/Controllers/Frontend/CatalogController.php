<?php

namespace Kommercio\Http\Controllers\Frontend;

use Illuminate\Support\Facades\Event;
use Kommercio\Events\CatalogQueryBuilder as CatalogQueryBuilderEvent;
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

        if(!$product->productDetail->active){
            return redirect()->back()->withErrors([trans(LanguageHelper::getTranslationKey('frontend.product.not_active'))]);
        }

        $view_name = ProjectHelper::findViewTemplate($product->getViewSuggestions());

        return view($view_name, [
            'product' => $product,
            'seoModel' => $product
        ]);
    }

    public function viewCategory($id)
    {
        $productCategory = ProductCategory::findOrFail($id);

        if(!$productCategory->active){
            return redirect()->back()->withErrors([trans(LanguageHelper::getTranslationKey('frontend.product_category.not_active'))]);
        }

        $qb = $productCategory->products();

        $event_results = Event::fire(new CatalogQueryBuilderEvent('product_category_products', $qb));

        //If not processed, build default query here
        if(!isset($event_results[0]) || empty($event_results[0])){
            $qb->joinDetail()->selectSelf()
                ->where('active', true)
                ->whereIn('visibility', [ProductDetail::VISIBILITY_EVERYWHERE, ProductDetail::VISIBILITY_CATALOG])
                ->orderBy('sort_order', 'ASC');
            $products = $qb->get();
        }else{
            $products = $event_results[0];
        }

        $view_name = ProjectHelper::findViewTemplate($productCategory->getViewSuggestions());

        return view($view_name, [
            'productCategory' => $productCategory,
            'products' => $products,
            'seoModel' => $productCategory
        ]);
    }
}
