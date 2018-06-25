<?php

namespace Kommercio\ShippingMethods;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Kommercio\Models\Address\Address;

class SameDayDelivery extends ShippingMethodAbstract implements ShippingMethodSettingsInterface {
    static public $table = 'shipping_same_day_delivery_configs';

    public function getAvailableMethods()
    {
        $methods = [
            'postal_code_delivery' => [
                'shipping_method_id' => $this->shippingMethod->id,
                'name' => $this->shippingMethod->name,
                'description' => null,
                'taxable' => $this->shippingMethod->taxable
            ],
        ];

        return $methods;
    }

    public function getPrices($options = null)
    {
        return parent::getPrices($options);
    }

    public function renderSettingView(Address $address)
    {
        $config = static::getConfig($address);
        $postalSettings = isset($config['postal_settings']) ? $config['postal_settings'] : null;

        return view('backend.shipping_method.same_day_delivery.setting_form', [
            'postalSettings' => $postalSettings,
            'shippingMethod' => $this->shippingMethod,
        ])->render();
    }

    public function processSettings(Request $request, Address $address)
    {
        $rules = [
            'postal_settings' => [
                'required',
                'string',
            ],
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }else{
            $type = $address->addressType;
            $id = $address->id;

            $qb = DB::table(static::$table)
                ->where('address_type', $type)
                ->where('address_id', $id);

            $config = [
                'postal_settings' => $request->input('postal_settings'),
            ];
            $data = [
                'address_type' => $type,
                'address_id' => $id,
                'data' => json_encode($config),
            ];

            if($qb->count() > 0){
                $qb->update($data);
            }else{
                DB::table(static::$table)->insert($data);
            }

            return redirect()->back()
                ->with('success', ['Postal Code Delivery configuration for ' . $address->name . ' is successfully saved.']);
        }
    }

    public function renderAdditionalSetting()
    {
        // Stub
    }

    public function processAdditionalSetting(Request $request)
    {
        // Stub
    }

    protected static function getAddressConfig($address)
    {
        $row = DB::table(static::$table)
            ->where('address_type', $address->addressType)
            ->where('address_id', $address->id)
            ->first();

        if ($row) {
            return json_decode($row->data, true);
        }

        return null;
    }

    public static function getConfig($address)
    {
        $config = static::getAddressConfig($address);

        if(!$config){
            $parent = $address->getParent();

            while($parent){
                $config = static::getAddressConfig($parent);

                if($config){
                    return $config;
                }else{
                    $parent = $parent->getParent();
                }
            }
        }

        return $config;
    }

    public static function additionalSettingValidation(Request $request)
    {
        return [
            'data.postal_settings' => 'required',
        ];
    }
}
