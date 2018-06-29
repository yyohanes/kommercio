<?php

namespace Kommercio\ShippingMethods;

use Carbon\Carbon;
use Cocur\Slugify\Slugify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Kommercio\Facades\EmailHelper;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\Address\Address;
use Kommercio\Models\Order\Order;
use Kommercio\Models\Store;
use Kommercio\Models\Tag;

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
        $fee = 0;

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

    /** @inheritdoc */
    public function handleNewOrder(Order $order)
    {
        // TODO: Find lowest address config. Currently hard coded to Country
        $address = $order->shippingInformation->country;
        $postalConfig = $this->getConfigByPostalCode(
            $order->shippingInformation->getAddress()['postal_code'],
            $address
        );

        if (empty($postalConfig)) return;

        // Tag order based on postal code zone
        $zoneTag = $this->getZoneTag($postalConfig['zone_name']);

        $order->tags()->detach($zoneTag);
        $order->tags()->attach($zoneTag);
        $order->load('tags');

        // Add lead time to delivery time
        $leadTime = round($postalConfig['lead_time'] / 60);
        $capacity = $postalConfig['capacity'];

        $now = Carbon::now();
        $dateTimeFrom = $order->delivery_date->setTimeFrom($now);

        $orderCount = $this->getOrderCount($postalConfig['zone_name'], $order->store, $dateTimeFrom);
        $availableInterval = $orderCount < $capacity ? $leadTime : ceil($orderCount / $capacity) * $leadTime;

        /** @var Carbon $deliveryDateTime */
        $deliveryDateTime = $now;
        $deliveryDateTime->addHour($availableInterval);

        $order->update([
            'delivery_date' => $deliveryDateTime,
        ]);

        // TODO: remove this when event is over
        // World cup delivery van
        if ($order->getShippingMethod() && $order->getShippingMethod()->class === 'SameDayDelivery') {
            $subject = 'There is new order #' . $order->reference;

            try {
                EmailHelper::sendMail(
                    'redha@kronoslogistic.com',
                    $subject,
                    ProjectHelper::getViewTemplate('order.third_party_logistic.confirmation'),
                    [
                        'order' => $order,
                    ],
                    'general'
                );
            } catch (\Exception $e) {
                \Log::error($e);
            }
        }
    }

    /** @inheritdoc */
    public function getDayAvailability(Carbon $datetime, array $options = [])
    {
        $times = [];
        $shippingProfile = $options['shippingProfile'] ?? [];
        $store = $options['store'] ?? null;

        if ($store && isset($shippingProfile['postal_code'])) {
            $postalConfig = $this->getConfigByPostalCode(
                $shippingProfile['postal_code'],
                $store->country
            );

            if ($postalConfig) {
                $leadTime = round($postalConfig['lead_time'] / 60);
                $limit = intval($postalConfig['limit']);
                $capacity = intval($postalConfig['capacity']);

                $now = Carbon::now();
                $dateTimeFrom = $datetime->setTimeFrom($now);
                $dateTimeTo = clone $dateTimeFrom;
                $dateTimeTo->addMinute($postalConfig['lead_time']);

                $orderCount = $this->getOrderCount($postalConfig['zone_name'], $store, $dateTimeFrom);

                if ($limit > 0 && $orderCount < $limit) {
                    $availableInterval = $orderCount < $capacity ? $leadTime : ceil($orderCount / $capacity) * $leadTime;

                    $nextHour = Carbon::now()->addHour($availableInterval);
                    $times[$nextHour->format('Y-m-d')] = [
                        $nextHour->format('H:i:s')
                    ];
                }
            }
        }

        return $times;
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
                $parsedConfig = $this->parseConfigLine(trim($configLine));
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

        $exploded = explode(';', $configLine);

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

    private function getOrderCount(string $zoneName, Store $store, $dateTimeFrom, $dateTimeTo = null)
    {
        $zoneTag = $this->getZoneTag($zoneName);

        $qb = Order
            ::whereHas('tags', function($qb) use ($zoneTag, $store) {
                $qb->where('id', $zoneTag->id);
            })
            ->where('store_id', $store->id)
            ->where('delivery_date', '>=', $dateTimeFrom)
            ->usageCounted();

        if ($dateTimeTo) {
            $qb->where('delivery_date', '<=', $dateTimeTo);
        }


        return $qb->count();
    }

    private function getZoneTag(string $zoneName): Tag
    {
        $zoneNameSlug = with(new Slugify())->slugify($zoneName);
        $zoneTag = Tag::findBySlug($zoneNameSlug);
        if (!$zoneTag) {
            $zoneTag = Tag::create([
                'name' => $zoneName,
            ]);
        }

        return $zoneTag;
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
