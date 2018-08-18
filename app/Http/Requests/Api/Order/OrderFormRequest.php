<?php

namespace Kommercio\Http\Requests\Api\Order;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Kommercio\Events\OrderEvent;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\Order\Order;
use Kommercio\Models\PaymentMethod\PaymentMethod;
use Kommercio\Models\ShippingMethod\ShippingMethod;
use Kommercio\Models\Store;

class OrderFormRequest extends \Illuminate\Foundation\Http\FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        $rules = static::getRules($this);

        return $rules;
    }

    public static function getRules(Request $request) {
        $rules = [
            'store_id' => [
                'required',
                'integer',
                'exists:stores,id',
            ],
            'billingProfile.full_name' => 'required',
            'billingProfile.email' => 'required|email',
            'billingProfile.phone_number' => 'required',
            'shippingProfile.full_name' => 'required',
            'shippingProfile.email' => 'required|email',
            'shippingProfile.phone_number' => 'required',
            'shippingProfile.country_id' => 'required',
            'shippingProfile.state_id' => 'descendant_address:state',
            'shippingProfile.city_id' => 'descendant_address:city',
            'shippingProfile.district_id' => 'descendant_address:district',
            'shippingProfile.area_id' => 'descendant_address:area',
            'shipping_method' => [
                'required',
                'exists:shipping_methods,id',
            ],
            'shipping_option' => [
                'required',
            ],
        ];

        $productRules = static::getProductRules($request);

        $rules = array_merge(
            $rules,
            $productRules
        );

        // Payment method additional validations
        $paymentMethod = PaymentMethod::findById($request->input('payment_method', 0));
        if ($paymentMethod) {
            $rules = array_merge(
                $rules,
                $paymentMethod->getProcessor()->getValidationRules()
            );

            // Validate store_id if payment method is limited by store
            if ($paymentMethod->stores->count() > 0) {
                $rules['store_id'][] = 'in:' . $paymentMethod->stores->implode('id', ',');
            }
        }

        return $rules;
    }

    public static function getFurtherRules(Request $request, Order $order) {
        $shippingMethod = ShippingMethod::findById($request->input('shipping_method'));
        $shippingMethodOptions = ShippingMethod::getShippingMethods([
            'order' => $order,
            'frontend' => true,
            'request' => $request,
            'show_all_active' => TRUE,
        ]);

        $rules = [
            'shipping_option' => [
                'required',
                'in:' . implode(',', array_keys($shippingMethodOptions)),
            ],
        ];

        if ($shippingMethod->requireAddress) {
            $rules['shippingProfile.address_1'] = [
                'required',
            ];

            if ($shippingMethod->requirePostalCode) {
                $rules['shippingProfile.postal_code'] = [
                    'required',
                ];
            }
        }

        if (ProjectHelper::getConfig('enable_delivery_date', FALSE)) {
            $rules['delivery_date'] = [
                'required',
                'date_format:Y-m-d',
                // Always block previous dates up to today
                'shipping_method_date:' . $request->input('shipping_method'),
            ];

            $store = Store::findById($request->input('store_id', 0));
            if ($store) $rules['delivery_date'][] = 'store_is_open:' . $store->id;
        }

        Event::fire(
            new OrderEvent(
                'frontend_rules_built',
                $order,
                [
                    'rules' => &$rules,
                    'request' => $request,
                ]
            )
        );

        return $rules;
    }

    protected static function getProductRules(Request $request) {
        $rules = [
            'quantities.*' => [
                'required',
                'min:1',
            ],
        ];

        // HACK
        // TODO: Remove this when world cup delivery is done
        // TODO: Planning to do event / listener based rules modification by project
        $isWorldCupDelivery = false;
        if ($request->input('shipping_method')) {
            $shippingMethod = ShippingMethod::findById($request->input('shipping_method'));
            $isWorldCupDelivery = $shippingMethod && $shippingMethod->class === 'SameDayDelivery';
        }

        foreach ($request->input('products', []) as $key => $productId) {
            $quantity = $request->input('quantities.' . $key, null);

            if (is_null($quantity)) continue;

            $productRules = [
                'required',
                'exists:products,id,deleted_at,NULL',
                'is_available',
                'is_active',
                'is_in_stock:' . $quantity,
                'is_purchaseable',
            ];

            $productRules[] = 'per_order_limit:' . $quantity . ',,' . $request->input('store_id');
            $productRules[] = 'today_order_limit:' . $quantity . ',,' . $request->input('store_id');

            if (ProjectHelper::getConfig('enable_delivery_date', FALSE)) {
                // HACK
                // TODO: Remove this when world cup delivery is done and uncomment below line
                // $productRules[] = 'delivery_order_limit:' . $quantity . ',,' . $request->input('delivery_date') . ',' . $request->input('store_id');
                if (!$isWorldCupDelivery) {
                    $productRules[] = 'delivery_order_limit:' . $quantity . ',,' . $request->input('delivery_date') . ',' . $request->input('store_id');
                }
            }

            $rules['products.' . $key] = $productRules;
        }

        return $rules;
    }
}
