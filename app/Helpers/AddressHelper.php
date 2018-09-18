<?php

namespace Kommercio\Helpers;

use Illuminate\Support\Facades\Cache;
use Kommercio\Models\Address\Area;
use Kommercio\Models\Address\City;
use Kommercio\Models\Address\Country;
use Kommercio\Models\Address\District;
use Kommercio\Models\Address\State;

class AddressHelper
{
    private $_alwaysRefresh = FALSE;

    public function getCountries($active_only=TRUE)
    {
        $cacheKey = 'address_countries_all';

        if ($this->_alwaysRefresh) {
            Cache::forget($cacheKey);
        }

        $countries = Cache::rememberForever(
            $cacheKey,
            function() {
                $qb = Country::orderBy('sort_order', 'ASC')->orderBy('name', 'ASC');

                return $qb->get();
            }
        );

        if($active_only){
            $countries = $countries->filter(function($country) {
                return $country->active;
            });
        }

        return $countries;
    }

    public function getCountryOptions($active_only=TRUE)
    {
        $options = $this->getCountries($active_only);

        return $options->pluck('name', 'id')->all();
    }

    public function getStates($country_id=null, $active_only=TRUE)
    {
        $country_id = intval($country_id);
        $cacheKey = 'address_states_all';

        if ($country_id) {
            $cacheKey = 'address_states_country_' . $country_id . '_states';
        }

        if ($this->_alwaysRefresh) {
            Cache::forget($cacheKey);
        }

        $states = Cache::rememberForever(
            $cacheKey,
            function() use ($country_id) {
                $qb = State::orderBy('name', 'ASC')->orderBy('name', 'ASC');

                if($country_id){
                    $qb->where('country_id', $country_id);
                }

                return $qb->get();
            }
        );

        if($active_only){
            $states = $states->filter(function($state) {
                return $state->active;
            });
        }

        return $states;
    }

    public function getStateOptions($country_id=null, $active_only=TRUE)
    {
        $country = intval($country_id) ? Country::find($country_id) : null;

        if(!$country || !$country->has_descendant){
            return [];
        }

        $options = $this->getStates($country_id, $active_only);

        return $options->pluck('name', 'id')->all();
    }

    public function getCities($state_id=null, $active_only=TRUE)
    {
        $state_id = intval($state_id);
        $cacheKey = 'address_cities_all';

        if ($state_id) {
            $cacheKey = 'address_cities_state_' . $state_id . '_cities';
        }

        if ($this->_alwaysRefresh) {
            Cache::forget($cacheKey);
        }

        $cities = Cache::rememberForever(
            $cacheKey,
            function() use ($state_id) {
                $qb = City::orderBy('name', 'ASC')->orderBy('name', 'ASC');

                if($state_id){
                    $qb->where('state_id', $state_id);
                }

                return $qb->get();
            }
        );

        if($active_only){
            $cities = $cities->filter(function($city) {
                return $city->active;
            });
        }

        return $cities;
    }

    public function getCityOptions($state_id=null, $active_only=TRUE)
    {
        $state = intval($state_id) ? State::find($state_id) : null;

        if(!$state || !$state->has_descendant){
            return [];
        }

        $options = $this->getCities($state_id, $active_only);

        return $options->pluck('name', 'id')->all();
    }

    public function getDistricts($city_id=null, $active_only=TRUE)
    {
        $city_id = intval($city_id);
        $cacheKey = 'address_districts_all';

        if ($city_id) {
            $cacheKey = 'address_districts_city_' . $city_id . '_districts';
        }

        if ($this->_alwaysRefresh) {
            Cache::forget($cacheKey);
        }

        $districts = Cache::rememberForever(
            $cacheKey,
            function() use ($city_id) {
                $qb = District::orderBy('name', 'ASC')->orderBy('name', 'ASC');

                if($city_id){
                    $qb->where('city_id', $city_id);
                }

                return $qb->get();
            }
        );

        if($active_only){
            $districts = $districts->filter(function($district) {
                return $district->active;
            });
        }

        return $districts;
    }

    public function getDistrictOptions($city_id=null, $active_only=TRUE)
    {
        $city = intval($city_id) ? City::find($city_id) : null;

        if(!$city || !$city->has_descendant){
            return [];
        }

        $options = $this->getDistricts($city_id, $active_only);

        return $options->pluck('name', 'id')->all();
    }

    public function getAreas($district_id=null, $active_only=TRUE)
    {
        $district_id = intval($district_id);
        $cacheKey = 'address_areas_all';

        if (!empty($district_id)) {
            $cacheKey = 'address_areas_district_' . $district_id . '_areas';
        }

        if ($this->_alwaysRefresh) {
            Cache::forget($cacheKey);
        }

        $areas = Cache::rememberForever(
            $cacheKey,
            function() use ($district_id) {
                $qb = Area::orderBy('name', 'ASC')->orderBy('name', 'ASC');

                if($district_id){
                    $qb->where('district_id', $district_id);
                }

                return $qb->get();
            }
        );

        if($active_only){
            $areas = $areas->filter(function($area) {
                return $area->active;
            });
        }

        return $areas;
    }

    public function getAreaOptions($district_id=null, $active_only=TRUE)
    {
        $district = intval($district_id) ? District::find($district_id) : null;

        if(!$district || !$district->has_descendant){
            return [];
        }

        $options = $this->getAreas($district_id, $active_only);

        return $options->pluck('name', 'id')->all();
    }

    public function getAddressFields()
    {
        return [
            'address_1',
            'address_2',
            'area_id',
            'district_id',
            'city_id',
            'custom_city',
            'state_id',
            'country_id',
            'postal_code',
        ];
    }

    public function getAddressFormat()
    {
        return [
            'address_1',
            'address_2',
            'area',
            'district',
            'city',
            'state',
            'country',
            'postal_code',
        ];
    }

    public function extractAddressFields($data)
    {
        $country = null;
        if(!empty($data['country_id'])){
            $country = Country::find($data['country_id']);
        }

        $addressFormat = $this->getAddressFormat();

        $addressElements = [];
        array_walk($addressFormat, function($value) use ($data, &$addressElements){
            if (!empty($data[$value])) {
                $addressElements[$value] = $data[$value];
            } else {
                $addressElements[$value] = null;
            }
        });

        if(!empty($data['area_id'])){
            $area = Area::find($data['area_id']);

            if($area){
                $addressElements['area'] = $area->name;
            }
        }

        if(!empty($data['district_id'])){
            $district = District::find($data['district_id']);

            if($district){
                $addressElements['district'] = $district->name;
            }
        }

        if(!empty($data['city_id'])){
            $city = City::find($data['city_id']);

            if($city){
                $addressElements['city'] = $city->name;
            }
        } elseif (!empty($data['custom_city'])) {
            $addressElements['city'] = $data['custom_city'];
        }

        if(!empty($data['state_id'])){
            $state = State::find($data['state_id']);

            if($state){
                $addressElements['state'] = $state?$state->name:'';
            }
        }

        if($country){
            $addressElements['country'] = $country?$country->name:'';
        }

        return $addressElements;
    }

    public function printAddress($data)
    {
        $addressElements = $this->extractAddressFields($data);

        foreach($addressElements as $idx => $addressElement){
            if(empty($addressElement)){
                unset($addressElements[$idx]);
                continue;
            }

            if (in_array($idx, ['address_1', 'address_2']) && isset($addressElements[$idx])) {
                $addressElements[$idx] = nl2br(htmlentities($addressElements[$idx]));
            } else {
                $addressElements[$idx] = htmlentities($addressElements[$idx]);
            }
        }

        $addressLineElements = [];

        // Print State, City, District and Area in one lineItems
        $locations = [];
        $oneLiners = ['state', 'city', 'district', 'area'];
        foreach ($oneLiners as $oneLiner) {
            if(!empty($addressElements[$oneLiner])){
                $locations[] = $addressElements[$oneLiner];
                unset($addressElements[$oneLiner]);
            }
        }

        if (!empty($locations)) {
            $addressLineElements['location'] = implode(', ', $locations);
        }

        if(isset($addressElements['country'])){
            $addressLineElements['country'] = $addressElements['country'];
            unset($addressElements['country']);
        }

        if(isset($addressElements['postal_code'])){
            $addressLineElements['postal_code'] = $addressElements['postal_code'];
            unset($addressElements['postal_code']);
        }

        if($addressElements){
            array_unshift($addressLineElements, implode(' ', $addressElements));
        }

        return implode('<br/>', $addressLineElements);
    }

    public function setAlwaysRefresh($active)
    {
        $this->_alwaysRefresh = $active;
    }
}
