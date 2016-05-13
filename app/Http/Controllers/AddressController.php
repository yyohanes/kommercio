<?php

namespace Kommercio\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Kommercio\Facades\AddressHelper;

class AddressController extends Controller
{
    public function options(Request $request, $type)
    {
        $options = [];

        $parent = $request->input('parent', null);
        $active_only = $request->input('active_only', 1) == 1;

        switch($type){
            case 'country':
                $options += AddressHelper::getCountryOptions($active_only);
                break;
            case 'state':
                $options += AddressHelper::getStateOptions($parent, $active_only);
                break;
            case 'city':
                $options += AddressHelper::getCityOptions($parent, $active_only);
                break;
            case 'district':
                $options += AddressHelper::getDistrictOptions($parent, $active_only);
                break;
            case 'area':
                $options += AddressHelper::getAreaOptions($parent, $active_only);
                break;
        }

        return response()->json($options);
    }
}
