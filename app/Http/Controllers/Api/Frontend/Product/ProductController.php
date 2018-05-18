<?php

namespace Kommercio\Http\Controllers\Api\Frontend\Product;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Http\Resources\Product\ProductCollection;
use Kommercio\Models\Product;
use Kommercio\Models\Store;

class ProductController extends Controller {
    public function products(Request $request) {
        $perPage = $request->get('per_page', 25);

        if ($request->filled('store_code')) {
            $store = Store::findByCode(
                $request->input('store_code')
            );
        } else {
            $store = ProjectHelper::getStoreByRequest($request);
        }

        /** @var Builder $qb */
        $qb = Product::productEntity()
            ->active($store)
            ->catalogVisible($store);

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
