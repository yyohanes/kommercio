<?php

namespace Kommercio\Http\Resources\ProductCategory;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductCategoryCollection extends ResourceCollection {
    public function toArray($request) {
        return ProductCategoryResource::collection($this->collection);
    }
}
