<?php

namespace Kommercio\Http\Controllers\Api\Frontend\Store;

use Illuminate\Http\Request;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Http\Controllers\Controller;
use Kommercio\Http\Resources\Store\StoreCollection;
use Kommercio\Models\Store;

class StoreController extends Controller {

    public function get(Request $request) {
        $qb = Store::orderBy('created_at', 'DESC');

        if ($request->filled('code')) {
            $codes = explode(',', $request->input('code'));
            $qb->whereIn('code', $codes);
        }

        $stores = $qb->get();

        $response = new StoreCollection($stores);

        return $response->response();
    }
}
