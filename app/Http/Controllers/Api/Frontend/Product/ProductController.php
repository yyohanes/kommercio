<?php

namespace Kommercio\Http\Controllers\Api\Frontend\Product;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Http\Resources\Product\ProductCollection;
use Kommercio\Models\Product;

class ProductController extends Controller {
    public function products(Request $request) {
        $perPage = $request->get('per_page', 25);

        /** @var Builder $qb */
        $qb = Product::productEntity();

        if ($request->get('categories')) {
            $categories = explode(',', $request->get('categories'));
            $qb->whereHas('categories', function($query) use ($categories) {
                $query->whereIn('id', $categories);
            });
        }

        $products = $qb->paginate($perPage);

        $products->appends($request->except('page'));

        $resources = new ProductCollection($products);

        return $resources->response();
    }
}
