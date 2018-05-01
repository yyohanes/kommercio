<?php

namespace Kommercio\Http\Resources\ProductCategory;

use Illuminate\Http\Resources\Json\Resource;

use Kommercio\Models\ProductCategory;

class ProductCategoryResource extends Resource {

    public function toArray($request) {
        /** @var ProductCategory $productCategory */
        $productCategory = $this->resource;

        return [
            'id' => $productCategory->id,
            'name' => $productCategory->name,
            'description' => $productCategory->description,
            'active' => !empty($productCategory->active),
            'slug' => $productCategory->slug,
            'metaTitle' => $productCategory->meta_title,
            'metaDescription' => $productCategory->meta_description,
            'sortOrder' => $productCategory->sort_order,
        ];
    }
}
