<?php

namespace Kommercio\ShippingMethods;

use GuzzleHttp\Client;
use Kommercio\Facades\LanguageHelper;
use Kommercio\Facades\KommercioAPIHelper;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\Address\City;
use Kommercio\Models\Address\Country;

class NCS extends ShippingMethodAbstract
{
    public function getAvailableMethods()
    {
        $methods = [
            'ONS' => [
                'shipping_method_id' => $this->shippingMethod->id,
                'name' => 'NCS ONS (One Night Service)',
                'description' => 'Layanan Kiriman 1 Hari',
                'taxable' => $this->shippingMethod->taxable
            ],
            'REG' => [
                'shipping_method_id' => $this->shippingMethod->id,
                'name' => 'NCS Regular',
                'description' => 'Layanan Reguler',
                'taxable' => $this->shippingMethod->taxable
            ],
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
            $destination = City::find($request->input('shipping_profile.city_id'));
        }else{
            //From saved order
            if($order && $order->store && !empty($order->store->getDefaultWarehouse()->city_id) && $order->shippingInformation){
                $country = Country::find($order->shippingInformation->country_id);
                $origin = City::find($order->store->getDefaultWarehouse()->city_id);
                $destination = City::find($order->shippingInformation->city_id);
            }
        }

        if(!empty($country) && $country->iso_code == 'ID' && !empty($origin) && !empty($destination)){
            // Find rate from Kommercio Master
            $client = new Client();
            $res = $client->request('GET', KommercioAPIHelper::getAPIUrl().'/ncs/rates', [
                'http_errors' => false,
                'query' =>  [
                    'api_token' => KommercioAPIHelper::getAPIToken(),
                    'destination_city' => $destination->master_id,
                    'origin_city' => $origin->master_id,
                ]
            ]);

            $orderWeight = (int) $order->getTotalWeight();

            if($res->getStatusCode() == 200) {
                $body = $res->getBody();

                $results = json_decode($body);

                if($results){
                    foreach($results as $serviceType => $service){
                        if(isset($methods[$serviceType])){
                            $return[$serviceType] = $methods[$serviceType];
                            $return[$serviceType]['price'] = [
                                'currency' => 'idr',
                                'amount' => $service->rate * ceil($orderWeight / 1000)
                            ];

                            if (!empty($service->edd)) {
                                $return[$serviceType]['description'] = trans_choice(LanguageHelper::getTranslationKey('order.shipping.estimated_working_day'), intval($service->edd), ['estimated' => intval($service->edd)]);
                            } else {
                                $return[$serviceType]['description'] = '';
                            }
                        }
                    }
                }
            }
        }

        return $return;
    }
}
