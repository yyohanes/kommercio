<?php

namespace Kommercio\Http\Controllers\Api\Frontend\Products;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Http\Resources\Products\ProductCollection;
use Kommercio\Models\Product;

class ProductController extends Controller {
    public function index(Request $request) {
        $perPage = $request->get('per_page', 25);

        /** @var Builder $qb */
        $qb = Product::productEntity();

        $products = $qb->paginate($perPage);

        $products->appends($request->except('page'));

        $resources = new ProductCollection($products);

        return $resources->response();
    }
}
