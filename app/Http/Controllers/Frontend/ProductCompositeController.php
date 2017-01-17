<?php

namespace Kommercio\Http\Controllers\Frontend;

use Illuminate\Http\Request;

use Kommercio\Facades\LanguageHelper;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Http\Requests;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\Product;
use Kommercio\Models\Product\Composite\ProductCompositeGroup;

class ProductCompositeController extends Controller
{
    public function viewProduct($slug, $product_slug)
    {
        $compositeGroup = ProductCompositeGroup::findBySlugOrFail($slug);
        $product = Product::whereTranslation('slug', $product_slug)->firstOrFail();

        if($check = $this->belongingCheck($product, $compositeGroup)){
            return $check;
        }

        $seoData = [
            'meta_title' => $product->name.' '.$compositeGroup->name
        ];

        $view_name = ProjectHelper::findViewTemplate($compositeGroup->getViewSuggestions());

        return view($view_name, [
            'product' => $product,
            'productCompositeGroup' => $compositeGroup,
            'seoData' => $seoData,
        ]);
    }

    protected function belongingCheck($product, $productCompositeGroup)
    {
        if(!$product->productCompositeGroups->pluck('id')->contains($productCompositeGroup->id)){
            return redirect()
                ->route('frontend.catalog.product.view', ['id' => $product->id])
                ->withErrors([
                    trans(LanguageHelper::getTranslationKey('frontend.product.composite.not_belong'))
                ]);
        }
    }
}
