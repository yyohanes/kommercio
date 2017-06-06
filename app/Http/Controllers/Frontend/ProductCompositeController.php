<?php

namespace Kommercio\Http\Controllers\Frontend;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Session;
use Kommercio\Facades\FrontendHelper;
use Kommercio\Facades\LanguageHelper;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Http\Requests;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Models\Product;
use Kommercio\Models\Product\Composite\ProductCompositeGroup;

class ProductCompositeController extends Controller
{
    public function viewProduct(Request $request, $slug, $product_slug)
    {
        $compositeGroup = ProductCompositeGroup::where('slug', $slug)->firstOrFail();
        $product = Product::whereTranslation('slug', $product_slug)->firstOrFail();

        if($request->has('line_item_id')){
            $order = FrontendHelper::getCurrentOrder();
            $lineItem = $order->findLineItem($request->input('line_item_id'));

            if($lineItem){
                $oldValues = old();

                if(empty($oldValues)){
                    $oldValues['quantity'] = $lineItem->quantity;

                    foreach($lineItem->children as $childLineItem){
                        if($childLineItem->productComposite){
                            $oldValues = array_add($oldValues, 'product_composite.'.$childLineItem->productComposite->id.'.'.$childLineItem->line_item_id, true);

                            foreach($childLineItem->productConfigurations as $productConfiguration){
                                $oldValues = array_add($oldValues, 'product_configuration.'.$childLineItem->line_item_id.'.'.$productConfiguration->id, $productConfiguration->pivot->value);
                            }
                        }
                    }

                    Session::flashInput($oldValues);
                }
            }
        }

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
