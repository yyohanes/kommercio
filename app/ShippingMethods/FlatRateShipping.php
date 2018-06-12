<?php

namespace Kommercio\ShippingMethods;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Kommercio\Facades\CurrencyHelper;
use Kommercio\Models\Address\Address;
use Kommercio\Models\Address\District;

class FlatRateShipping extends ShippingMethodAbstract implements ShippingMethodSettingsInterface
{
    protected $table = 'shipping_flat_rates';

    public function getAvailableMethods()
    {
        $methods = [
            'flat_rate' => [
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
        $order = isset($options['order'])?$options['order']:null;
        $request = isset($options['request'])?$options['request']:null;

        if($order || $request){
            $currency = $order->currency?:CurrencyHelper::getCurrentCurrency()['code'];

            $price = null;

            $lowest_address_type = 'country';

            $address_id = null;

            if($order->shippingInformation->country_id){
                if($order->shippingInformation->area_id){
                    $lowest_address_type = 'area';
                }elseif($order->shippingInformation->district_id){
                    $lowest_address_type = 'district';
                }elseif($order->shippingInformation->city_id){
                    $lowest_address_type = 'city';
                }elseif($order->shippingInformation->state_id){
                    $lowest_address_type = 'state';
                }

                $address_id = $order->shippingInformation->{$lowest_address_type.'_id'};
            }elseif($request){
                if($request->has('shipping_profile.area_id')){
                    $lowest_address_type = 'area';
                }elseif($request->has('shipping_profile.district_id')){
                    $lowest_address_type = 'district';
                }elseif($request->has('shipping_profile.city_id')){
                    $lowest_address_type = 'city';
                }elseif($request->has('shipping_profile.state_id')){
                    $lowest_address_type = 'state';
                }

                $address_id = $request->input('shipping_profile.'.$lowest_address_type.'_id');
            }

            $model = Address::getClassNameByType($lowest_address_type);

            $address = $model::find($address_id);

            if($address){
                $rate = $this->findRate($address);

                if($rate){
                    $price = $rate->price;
                }
            }

            if(is_null($price)){
                return [];
            }

            $methods = $this->getAvailableMethods();

            foreach($methods as &$method){
                $method['price'] = [
                    'currency' => $currency,
                    'amount' => CurrencyHelper::convert($price, $rate->currency, $currency)
                ];
            }

            return $methods;
        }

        return [];
    }

    public function renderSettingView(Address $address)
    {
        $rate = $this->getAddressRate($address);

        return view('backend.shipping_method.FlatRate.rate_setting_form', ['rate' => $rate, 'shippingMethod' => $this->shippingMethod])->render();
    }

    public function processSettings(Request $request, Address $address)
    {
        $type = $address->addressType;
        $id = $address->id;

        $rules = [
            'price' => 'numeric|min:0',
        ];

        if(empty($request->input('price')) && !is_numeric($request->input('price'))){
            $request->request->set('price', null);
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }else{
            $rate = $this->getAddressRate($address);

            if($rate){
                DB::table($this->table)->where('address_type', $type)
                    ->where('address_id', $id)->update([
                    'address_type' => $type,
                    'address_id' => $id,
                    'price' => $request->input('price', null),
                    'currency' => $request->input('currency', null)
                ]);
            }else{
                DB::table($this->table)->insert([
                    'address_type' => $type,
                    'address_id' => $id,
                    'price' => $request->input('price', null),
                    'currency' => $request->input('currency', null)
                ]);
            }

            return redirect()->back()
                ->with('success', ['Shipping price is successfully saved.']);
        }
    }

    protected function getAddressRate($address)
    {
        $rate = DB::table($this->table)
            ->where('address_type', $address->addressType)
            ->where('address_id', $address->id)
            ->whereNotNull('price')->first();

        return $rate;
    }

    protected function findRate($address)
    {
        $rate = $this->getAddressRate($address);

        if(!$rate){
            $parent = $address->getParent();

            while($parent){
                $rate = $this->getAddressRate($parent);

                if($rate){
                    return $rate;
                }else{
                    $parent = $parent->getParent();
                }
            }
        }

        return $rate;
    }

    public function renderAdditionalSetting()
    {
        return null;
    }

    public function processAdditionalSetting(Request $request)
    {
        // Stub
    }

    //Statics
    public static function additionalSettingValidation(Request $request)
    {
        return [];
    }
}
