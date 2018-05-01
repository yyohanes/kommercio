<?php

namespace Kommercio\Http\Resources\Products;

use Illuminate\Http\Resources\Json\Resource;

use Kommercio\Http\Resources\Media\ImageCollection;
use Kommercio\Http\Resources\ProductCategory\ProductCategoryCollection;
use Kommercio\Http\Resources\ProductCategory\ProductCategoryResource;
use Kommercio\Models\Product;

class ProductResource extends Resource {
    public function toArray($request) {
        /** @var Product $product */
        $product = $this->resource;
        $productDetail = $product->productDetail;

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
            'currency' => $productDetail->currency,
            'taxable' => !empty($productDetail->taxable),
            'price' => [
                'retailPrice' => $product->getRetailPrice(),
                'retailPriceWithTax' => $product->getRetailPrice(true),
                'netPrice' => $product->getNetPrice(),
                'netPriceWithTax' => $product->getNetPrice(true),
            ],
            'defaultCategory' => new ProductCategoryResource($product->defaultCategory),
            'categories' => new ProductCategoryCollection($product->categories),
        ];
    }
}
