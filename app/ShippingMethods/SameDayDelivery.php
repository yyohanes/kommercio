<?php

namespace Kommercio\ShippingMethods;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Kommercio\Models\Address\Address;
use Kommercio\Models\Order\Order;

class SameDayDelivery extends ShippingMethodAbstract implements ShippingMethodSettingsInterface
{
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
        $fee = 14.02;

        $methods = $this->getAvailableMethods();
        foreach ($methods as $methodId => &$method) {
            $method['price'] = [
                'currency' => 'sgd',
                'amount' => $fee
            ];
        }

        return $methods;
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
        } else {
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

            if ($qb->count() > 0) {
                $qb->update($data);
            } else {
                DB::table(static::$table)->insert($data);
            }

            return redirect()->back()
                ->with('success', ['Postal Code Delivery configuration for ' . $address->name . ' is successfully saved.']);
        }
    }

    public function handleNewOrder(Order $order)
    {

    }

    public function renderAdditionalSetting()
    {
        // Stub
    }

    public function processAdditionalSetting(Request $request)
    {
        // Stub
    }

    /**
     * @param string $postalCode
     * @return array|null
     */
    public function getConfigByPostalCode(string $postalCode, Address $address)
    {
        $config = static::getConfig($address);
        $postalSettings = isset($config['postal_settings']) ? $config['postal_settings'] : null;

        if (empty($postalSettings)) return null;

        $configLines = explode(PHP_EOL, $postalSettings);

        foreach ($configLines as $configLine) {
            try {
                $parsedConfig = $this->parseConfigLine($configLine);
                $pattern = $parsedConfig['postal_pattern'];

                if (preg_match('/' . $pattern . '/i', $postalCode))
                    return $parsedConfig;
            } catch (\Exception $e) {
                // Do nothing
            }
        }

        return null;
    }

    /**
     * @param string $configLine
     * @return array
     * @throws \Exception
     */
    protected function parseConfigLine(string $configLine): array
    {
        $map = [
            'zone_name',
            'postal_pattern',
            'lead_time',
            'capacity',
            'price',
            'minimum_amount',
            'maximum_amount',
            'free_shipping_minimum',
            'limit',
        ];

        $exploded = explode('|', $configLine);

        if (count($exploded) !== count($map)) {
            throw new \Exception('Config: "' . $configLine . '". ' . count($map) . ' parameters are needed.');
        }

        $config = [];

        foreach ($exploded as $idx => $configItem) {
            if (!isset($map[$idx])) {
                throw new \Exception('Config: "' . $configLine . '". Out of bound config at index ' . $idx);
            }

            $config[$map[$idx]] = $configItem;
        }

        return $config;
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

        if (!$config) {
            $parent = $address->getParent();

            while ($parent) {
                $config = static::getAddressConfig($parent);

                if ($config) {
                    return $config;
                } else {
                    $parent = $parent->getParent();
                }
            }
        }

        return $config;
    }

    public static function additionalSettingValidation(Request $request)
    {
        return [];
    }
}
