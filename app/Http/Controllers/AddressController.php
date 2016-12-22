<?php

namespace Kommercio\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Kommercio\Facades\AddressHelper;
use Kommercio\Facades\LanguageHelper;

class AddressController extends Controller
{
    public function options(Request $request, $type)
    {
        $options = [];

        $parent = $request->input('parent', null);
        $active_only = $request->input('active_only', 1) == 1;

        $first_option = $request->input('first_option', 0) == 1;

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
                $options = AddressHelper::getAreaOptions($parent, $active_only);
                break;
        }

        return response()->json($options);
    }
}
