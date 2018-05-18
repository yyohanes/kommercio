<?php

namespace Kommercio\Http\Resources\Product;

use Illuminate\Http\Resources\Json\Resource;

use Kommercio\Facades\CurrencyHelper;
use Kommercio\Http\Resources\Media\ImageCollection;
use Kommercio\Http\Resources\ProductCategory\ProductCategoryCollection;
use Kommercio\Http\Resources\ProductCategory\ProductCategoryResource;
use Kommercio\Http\Resources\ProductComposite\ProductCompositeResource;
use Kommercio\Models\Product;

class ProductResource extends Resource {
    protected $hidden = [];

    public function toArray($request) {
        /** @var Product $product */
        $product = $this->resource;
        $productDetail = $product->productDetail;
        $currency = CurrencyHelper::getCurrency($productDetail->currency);

        return [
            'id' => $product->id,
            'sku' => $product->sku,
            'type' => $product->type,
            'combinationType' => $product->combination_type,
            'name' => $product->name,
            'slug' => $product->slug,
            'descriptionShort' => $product->description_short,
            'description' => $product->description,
            'metaTitle' => $product->meta_title,
            'metaDescription' => $product->meta_description,
            'locale' => $product->locale,
            'thumbnails' => new ImageCollection($product->thumbnails),
            'images' => new ImageCollection($product->images),
            'dimensions' => [
                'width' => $product->width,
                'length' => $product->length,
                'depth' => $product->depth,
                'weight' => $product->weight,
            ],
            'new' => !empty($productDetail->new),
            'visibility' => $productDetail->visibility,
            'available' => $productDetail->available,
            'active' => !empty($productDetail->active),
            'taxable' => !empty($productDetail->taxable),
            'price' => [
                'retailPrice' => $product->getRetailPrice(),
                'retailPriceWithTax' => $product->getRetailPrice(true),
                'netPrice' => $product->getNetPrice(),
                'netPriceWithTax' => $product->getNetPrice(true),
                'currency' => [
                    'symbol' => $currency['symbol'],
                    'iso' => $currency['iso'],
                    'thousandSeparator' => $currency['thousand_separator'],
                    'decimalSeparator' => $currency['decimal_separator'],
                ],
            ],
            'defaultCategory' => new ProductCategoryResource($product->defaultCategory),
            'categories' => new ProductCategoryCollection($product->categories),
            'composites' => $this->when(
                !in_array('composites', $this->hidden),
                    $this->getProductComposites($product)
                ),
        ];
    }

    /**
     * @param Product $product
     * @return \Illuminate\Support\Collection
     */
    protected function getProductComposites(Product $product) {
        $productCompositeGroup = $product->productCompositeGroup;

        if (!$productCompositeGroup) return collect([]);

        return ProductCompositeResource::collection($productCompositeGroup->composites);
    }
}
