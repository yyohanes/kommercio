<?php

namespace Kommercio\ShippingMethods;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Kommercio\Facades\CurrencyHelper;
use Kommercio\Models\Address\Address;

class DHL extends ShippingMethodAbstract implements ShippingMethodSettingsInterface
{
    static public $table = 'shipping_dhl_configs';

    public function validate($options = null)
    {
        // Currently, DHL is enabled only for configuration
        return false;
    }

    public function getAvailableMethods()
    {
        $methods = [
            'dhl' => [
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
        $flatRate = new FlatRateShipping();
        $prices = $flatRate->getPrices($options);

        return $prices;
    }

    public function renderSettingView(Address $address)
    {
        $config = static::getConfig($address);
        $currencyOptions = CurrencyHelper::getCurrencyOptions();
        $regionCodeOptions = array_combine(static::getRegionCodes(), static::getRegionCodes());
        $dutyPaymentTypeOptions = array_combine(static::getDutyPaymentTypes(), static::getDutyPaymentTypes());

        return view('backend.shipping_method.dhl.setting_form', [
            'dhlName' => isset($config['dhlName']) ? $config['dhlName'] : null,
            'regionCode' => isset($config['regionCode']) ? $config['regionCode'] : null,
            'dutyPaymentType' => isset($config['dutyPaymentType']) ? $config['dutyPaymentType'] : null,
            'fallbackCityName' => isset($config['fallbackCityName']) ? $config['fallbackCityName'] : null,
            'overrideCityName' => isset($config['overrideCityName']) ? $config['overrideCityName'] : null,
            'dutiableMinimum' => isset($config['dutiableMinimum']) ? $config['dutiableMinimum'] : null,
            'dutiableCurrency' => isset($config['dutiableCurrency']) ? $config['dutiableCurrency'] : CurrencyHelper::getCurrentCurrency()['iso'],
            'countryCode' => isset($config['countryCode']) ? $config['countryCode'] : null,
            'currencyOptions' => $currencyOptions,
            'regionCodeOptions' => $regionCodeOptions,
            'shippingMethod' => $this->shippingMethod,
            'dutyPaymentTypeOptions' => $dutyPaymentTypeOptions,
        ])->render();
    }

    public function processSettings(Request $request, Address $address)
    {
        $type = $address->addressType;
        $id = $address->id;
        $currencyOptions = CurrencyHelper::getCurrencyOptions();

        $rules = [
            'dhlName' => [
                'nullable',
                'string',
            ],
            'dutiableMinimum' => [
                'nullable',
                'min:0',
            ],
            'regionCode' => [
                'required',
                'in:' . implode(',', array_values(static::getRegionCodes())),
            ],
            'dutyPaymentType' => [
                'required',
                'in:' . implode(',', array_values(static::getDutyPaymentTypes())),
            ],
            'dutiableCurrency' => 'in:' . implode(',', array_keys($currencyOptions)),
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }else{
            $qb = DB::table(static::$table)
                ->where('address_type', $type)
                ->where('address_id', $id);

            $config = [
                'dhlName' => $request->input('dhlName'),
                'regionCode' => $request->input('regionCode'),
                'countryCode' => $request->input('countryCode'),
                'dutyPaymentType' => $request->input('dutyPaymentType'),
                'overrideCityName' => $request->input('overrideCityName'),
                'fallbackCityName' => $request->input('fallbackCityName'),
                'dutiableMinimum' => $request->input('dutiableMinimum'),
                'dutiableCurrency' => $request->input('dutiableCurrency'),
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
                ->with('success', ['DHL configuration for ' . $address->name . ' is successfully saved.']);
        }
    }

    public function renderAdditionalSetting()
    {
        return view('backend.shipping_method.dhl.additional_setting_form', [
            'shippingMethod' => $this->shippingMethod,
        ])->render();
    }

    public function processAdditionalSetting(Request $request)
    {
        // stub
    }

    //Statics
    public static function getRegionCodes()
    {
        return [
            'AP',
            'AM',
            'EU',
        ];
    }

    public static function getDutyPaymentTypes()
    {
        return [
            'S',
            'R',
            'T',
        ];
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
            'data.shipper_account_number' => 'required',
            'data.site_id' => 'required',
            'data.company_name' => 'required',
            'data.contact_person' => 'required',
            'data.contact_number' => 'required',
            'data.contact_email' => 'nullable|email',
        ];
    }
}

