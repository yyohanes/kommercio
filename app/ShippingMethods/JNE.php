<?php

namespace Kommercio\ShippingMethods;

use GuzzleHttp\Client;
use Kommercio\Facades\LanguageHelper;
use Kommercio\Models\Address\City;
use Kommercio\Models\Address\District;
use Kommercio\Models\Order\Order;
use Kommercio\Models\ShippingMethod\ShippingMethod;

class JNE implements ShippingMethodInterface
{
    protected $shippingMethod;

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

    public function validate($options = null)
    {
        $valid = TRUE;

        return $valid;
    }

    public function setShippingMethod(ShippingMethod $shippingMethod)
    {
        $this->shippingMethod = $shippingMethod;
    }

    public function getPrices($options = null)
    {
        $return = [];
        $methods = $this->getAvailableMethods();

        $order = $options['order']?:null;

        if($order && $order->store){
            $origin = City::findOrFail($order->store->getDefaultWarehouse()->city_id);
            $destination = $order->shippingInformation->district_id?District::findOrFail($order->shippingInformation->district_id):City::findOrFail($order->shippingInformation->city_id);

            //Call Raja Ongkir API
            $client = new Client();
            $res = $client->post('http://pro.rajaongkir.com/api/cost', [
                'http_errors' => false,
                'form_params' =>  [
                    'key' => '195fa4351871a434f7d9fefaadedef05',
                    'origin' => $origin->master_id,
                    'originType' => 'city',
                    'destination' => $destination->master_id,
                    'destinationType' => $order->shippingInformation->district_id?'subdistrict':'city',
                    'weight' => $order->getTotalWeight(),
                    'courier' => 'jne',
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

    public function beforePlaceOrder(Order $order)
    {

    }
}