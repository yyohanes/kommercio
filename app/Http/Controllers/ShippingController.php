<?php

namespace Kommercio\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Kommercio\Facades\AddressHelper;
use Kommercio\Models\ShippingMethod\ShippingMethod;

class ShippingController extends Controller
{
    public function options(Request $request)
    {
        $return = [];

        $shippingOptions = ShippingMethod::getShippingMethods();

        foreach($shippingOptions as $shippingOption){
            $return[$shippingOption['shipping_method_id']] = [
                'name' => $shippingOption['name'],
                'price' => $shippingOption['price']['amount']
            ];
        }

        return response()->json($return);
    }
}
