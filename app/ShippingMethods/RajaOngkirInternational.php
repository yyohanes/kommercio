<?php

namespace Kommercio\ShippingMethods;

use GuzzleHttp\Client;
use Kommercio\Facades\KommercioAPIHelper;
use Kommercio\Facades\LanguageHelper;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\Address\City;
use Kommercio\Models\Address\Country;
use Kommercio\Models\Address\District;
use Kommercio\Models\Order\Order;
use Kommercio\Models\ShippingMethod\ShippingMethod;

class RajaOngkirInternational implements ShippingMethodInterface
{
    protected $shippingMethod;

    public function getAvailableMethods()
    {
        $methods = [
            'tiki' => [
                'shipping_method_id' => $this->shippingMethod->id,
                'name' => 'TIKI',
                'description' => null,
                'taxable' => $this->shippingMethod->taxable
            ],
            'pos' => [
                'shipping_method_id' => $this->shippingMethod->id,
                'name' => 'Pos Indonesia',
                'description' => null,
                'taxable' => $this->shippingMethod->taxable
            ]
        ];

        return $methods;
    }

    public function validate($options = null)
    {
        $valid = TRUE;

        return $valid;
    }

    public function requireAddress()
    {
        return TRUE;
    }

    public function setShippingMethod(ShippingMethod $shippingMethod)
    {
        $this->shippingMethod = $shippingMethod;
    }

    public function getPrices($options = null)
    {
        $return = [];
        $methods = $this->getAvailableMethods();

        $order = isset($options['order'])?$options['order']:null;

        //From request (if from backend)
        $request = isset($options['request'])?$options['request']:null;
        if($request){
            $origin = City::find(ProjectHelper::getStoreByRequest($request)->getDefaultWarehouse()->city_id);
            $destination = Country::find($request->input('shipping_profile.country_id'));
        }

        //From saved order
        if($order && $order->store && !empty($order->store->getDefaultWarehouse()->city_id) && $order->shippingInformation){
            $origin = City::find($order->store->getDefaultWarehouse()->city_id);
            $destination = Country::find($order->shippingInformation->country_id);
        }

        if($destination->iso_code != 'ID' && !empty($origin) && !empty($destination)){
            //Call Raja Ongkir API
            $client = new Client();
            $res = $client->request('GET', KommercioAPIHelper::getAPIUrl().'/rajaongkir/rates/international', [
                'http_errors' => false,
                'query' =>  [
                    'city_id' => $origin->master_id,
                    'country_id' => $destination->master_id,
                    'weight' => (int) $order->getTotalWeight(),
                    'api_token' => KommercioAPIHelper::getAPIToken(),
                ],
            ]);

            if($res->getStatusCode() == 200) {
                $body = $res->getBody();

                $results = json_decode($body);

                if($results){
                    $tiki = 0;
                    $pos = 0;

                    foreach($results as $service){
                        if(isset($methods[$service->code])){
                            foreach($service->costs as $cost){
                                if($service->code == 'tiki'){
                                    if($cost->service == 'Paket'){
                                        $tiki = $cost->cost;
                                    }
                                }elseif($service->code == 'pos'){
                                    if($cost->service == 'EMS BARANG'){
                                        $pos = $cost->cost;
                                    }
                                }
                            }
                        }
                    }

                    if($tiki){
                        $return['tiki'] = $methods['tiki'];
                        $return['tiki']['price'] = [
                            'currency' => 'idr',
                            'amount' => $tiki
                        ];
                    }

                    if($pos){
                        $return['pos'] = $methods['pos'];
                        $return['pos']['price'] = [
                            'currency' => 'idr',
                            'amount' => $pos
                        ];
                    }
                }
            }
        }

        return $return;
    }

    public function beforePlaceOrder(Order $order)
    {

    }
}