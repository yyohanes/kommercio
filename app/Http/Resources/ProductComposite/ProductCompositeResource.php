<?php

namespace Kommercio\Http\Resources\ProductComposite;

use Illuminate\Http\Resources\Json\Resource;

use Kommercio\Http\Resources\Product\NestedProductResource;
use Kommercio\Models\Product\Composite\ProductComposite;

class ProductCompositeResource extends Resource {

    public function toArray($request) {
        /** @var ProductComposite $productComposite */
        $productComposite = $this->resource;
        $productSelections = $this->getProductSelections($productComposite);
        $defaultProducts = $productComposite->getDefaultProducts();

        return [
            'id' => $productComposite->id,
            'name' => $productComposite->name,
            'label' => $productComposite->label,
            'slug' => $productComposite->slug,
            'minimum' => $productComposite->minimum + 0,
            'maximum' => $productComposite->maximum + 0,
            'isFree' => !empty($productComposite->free),
            'sortOrder' => $productComposite->sort_order,
            'productSelections' => $productSelections,
            'defaultProducts' => $this->when(
                    $defaultProducts->count() > 0,
                    $defaultProducts->map(
                        function($defaultProduct) {
                            return [
                                'id' => $defaultProduct->id,
                                'quantity' => $defaultProduct->pivot->quantity,
                                'sortOrder' => $defaultProduct->pivot->sort_order,
                            ];
                        }
                    )
                ),
        ];
    }

    /**
     * @param ProductComposite $productComposite
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    protected function getProductSelections(ProductComposite $productComposite) {
        $productSelections = $productComposite->getProductSelection();
        $productSelectionCollection = NestedProductResource::collection($productSelections);

        return $productSelectionCollection;
    }
}
