<?php

namespace Kommercio\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Kommercio\Facades\AddressHelper;
use Kommercio\Facades\LanguageHelper;

class AddressController extends Controller
{
    public function options(Request $request, string $type)
    {
        $options = $this->getOptions($request, $type);

        return response()->json($options);
    }

    /**
     * @param Request $request
     * @param string $type
     * @return array
     */
    protected function getOptions(Request $request, string $type)
    {
        $options = [];

        $parent = $request->input('parent', null);
        $active_only = $request->input('active_only', true);
        $first_option = $request->input('first_option', false);

        switch($type){
            case 'country':
                if($first_option){
                    $options = [null => trans(LanguageHelper::getTranslationKey('order.address.select_country'))];
                }
                $options += AddressHelper::getCountryOptions($active_only);
                break;
            case 'state':
                if($first_option){
                    $options = [trans(LanguageHelper::getTranslationKey('order.address.select_state'))];
                }
                $options += AddressHelper::getStateOptions($parent, $active_only);
                break;
            case 'city':
                if($first_option){
                    $options = [trans(LanguageHelper::getTranslationKey('order.address.select_city'))];
                }
                $options += AddressHelper::getCityOptions($parent, $active_only);
                break;
            case 'district':
                if($first_option){
                    $options = [trans(LanguageHelper::getTranslationKey('order.address.select_district'))];
                }
                $options += AddressHelper::getDistrictOptions($parent, $active_only);
                break;
            case 'area':
                if($first_option){
                    $options = [trans(LanguageHelper::getTranslationKey('order.address.select_area'))];
                }
                $options += AddressHelper::getAreaOptions($parent, $active_only);
                break;
        }

        return $options;
    }
}
