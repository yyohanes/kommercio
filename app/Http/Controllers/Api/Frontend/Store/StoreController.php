<?php

namespace Kommercio\Http\Controllers\Api\Frontend\Store;

use Illuminate\Http\Request;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Http\Resources\Store\StoreCollection;
use Kommercio\Models\Store;

class StoreController extends Controller {

    public function stores(Request $request) {
        $qb = Store::orderBy('created_at', 'DESC');

        $stores = $qb->get();

        $response = new StoreCollection($stores);

        return $response->response();
    }
}
