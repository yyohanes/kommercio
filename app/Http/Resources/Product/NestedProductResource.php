<?php

namespace Kommercio\Http\Resources\Product;

class NestedProductResource extends ProductResource {
    protected $hidden = [
        'composites',
    ];
}
