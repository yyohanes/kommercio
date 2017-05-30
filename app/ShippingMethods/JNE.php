<?php

namespace Kommercio\ShippingMethods;

use GuzzleHttp\Client;
use Kommercio\Facades\LanguageHelper;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\Address\City;
use Kommercio\Models\Address\Country;
use Kommercio\Models\Address\District;
use Kommercio\Models\Order\Order;
use Kommercio\Models\ShippingMethod\ShippingMethod;

class JNE extends ShippingMethodAbstract
{
    public function getAvailableMethods()
    {
        $methods = [
            'CTC' => [
                'shipping_method_id' => $this->shippingMethod->id,
                'name' => 'JNE REG',
                'description' => 'Layanan Reguler',
                'taxable' => $this->shippingMethod->taxable
            ],
            'CTCOKE' => [
                'shipping_method_id' => $this->shippingMethod->id,
                'name' => 'JNE OKE',
                'description' => 'Ongkos Kirim Ekonomis',
                'taxable' => $this->shippingMethod->taxable
            ],
            'CTCYES' => [
                'shipping_method_id' => $this->shippingMethod->id,
                'name' => 'JNE YES',
                'description' => 'Yakin Esok Sampai',
                'taxable' => $this->shippingMethod->taxable
            ],
            'OKE' => [
                'shipping_method_id' => $this->shippingMethod->id,
                'name' => 'JNE OKE',
                'description' => 'Ongkos Kirim Ekonomis',
                'taxable' => $this->shippingMethod->taxable
            ],
            'REG' => [
                'shipping_method_id' => $this->shippingMethod->id,
                'name' => 'JNE REG',
                'description' => 'Layanan Reguler',
                'taxable' => $this->shippingMethod->taxable
            ],
            'YES' => [
                'shipping_method_id' => $this->shippingMethod->id,
                'name' => 'JNE YES',
                'description' => 'Yakin Esok Sampai',
                'taxable' => $this->shippingMethod->taxable
            ]
        ];

        return $methods;
    }

    public function getPrices($options = null)
    {
        $return = [];
        $methods = $this->getAvailableMethods();

        $order = isset($options['order'])?$options['order']:null;

        //From request (if from backend)
        $request = isset($options['request'])?$options['request']:null;
        if(!$options['frontend'] && $request && !empty($order->store->getDefaultWarehouse()->city_id)){
            $country = Country::find(ProjectHelper::getStoreByRequest($request)->getDefaultWarehouse()->country_id);
            $origin = City::find(ProjectHelper::getStoreByRequest($request)->getDefaultWarehouse()->city_id);
            $destination = $request->has('shipping_profile.district_id')?District::find($request->input('shipping_profile.district_id')):City::find($request->input('shipping_profile.city_id'));
            $destinationType = $request->has('shipping_profile.district_id')?'subdistrict':'city';
        }else{
            //From saved order
            if($order && $order->store && !empty($order->store->getDefaultWarehouse()->city_id) && $order->shippingInformation){
                $country = Country::find($order->shippingInformation->country_id);
                $origin = City::find($order->store->getDefaultWarehouse()->city_id);
                $destination = $order->shippingInformation->district_id?District::find($order->shippingInformation->district_id):City::find($order->shippingInformation->city_id);
                $destinationType = $order->shippingInformation->district_id?'subdistrict':'city';
            }
        }

        if(!empty($country) && $country->iso_code == 'ID' && !empty($origin) && !empty($destination)){
            //Call Raja Ongkir API
            $client = new Client();
            $res = $client->request('POST', 'http://pro.rajaongkir.com/api/cost', [
                'http_errors' => false,
                'form_params' =>  [
                    'origin' => $origin->master_id,
                    'originType' => 'city',
                    'destination' => $destination->master_id,
                    'destinationType' => $destinationType,
                    'weight' => (int) $order->getTotalWeight(),
                    'courier' => 'jne',
                ],
                'headers' =>  [
                    'key' => '195fa4351871a434f7d9fefaadedef05',
                ]
            ]);

            if($res->getStatusCode() == 200) {
                $body = $res->getBody();

                $results = json_decode($body);

                if($results && $results->rajaongkir->results){
                    foreach(array_shift($results->rajaongkir->results)->costs as $cost){
                        if(isset($methods[$cost->service])){
                            $return[$cost->service] = $methods[$cost->service];
                            $return[$cost->service]['description'] = ' '.trans_choice(LanguageHelper::getTranslationKey('order.shipping.estimated_working_day'), $cost->cost[0]->etd, ['estimated' => $cost->cost[0]->etd]);
                            $return[$cost->service]['price'] = [
                                'currency' => 'idr',
                                'amount' => $cost->cost[0]->value
                            ];
                        }
                    }
                }
            }
        }

        return $return;
    }
}