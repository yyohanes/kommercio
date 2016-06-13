<?php

namespace Kommercio\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Kommercio\Facades\OrderHelper;
use Kommercio\Models\ShippingMethod\ShippingMethod;

class ShippingController extends Controller
{
    public function options(Request $request)
    {
        $return = [];

        $order = OrderHelper::createDummyOrderFromRequest($request);

        $shippingOptions = ShippingMethod::getShippingMethods([
            'subtotal' => $order->calculateSubtotal()
        ]);

        foreach($shippingOptions as $idx=>$shippingOption){
            $return[$idx] = [
                'shipping_method_id' => $shippingOption['shipping_method_id'],
                'name' => $shippingOption['name'],
                'price' => $shippingOption['price'],
                'taxable' => $shippingOption['taxable']
            ];
        }

        return response()->json($return);
    }
}
