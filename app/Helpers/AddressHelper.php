<?php

namespace Kommercio\Helpers;

use Kommercio\Models\Address\Area;
use Kommercio\Models\Address\City;
use Kommercio\Models\Address\Country;
use Kommercio\Models\Address\District;
use Kommercio\Models\Address\State;

class AddressHelper
{
    private $_countries;
    private $_states;
    private $_cities;
    private $_districts;
    private $_areas;
    private $_alwaysRefresh = FALSE;

    public function getCountries($active_only=TRUE)
    {
        if(!isset($this->_countries) || $this->_alwaysRefresh){
            $qb = Country::orderBy('sort_order', 'ASC')->orderBy('name', 'ASC');

            if($active_only){
                $qb->active();
            }

            $this->_countries = $qb->get();
        }

        return $this->_countries;
    }

    public function getCountryOptions($active_only=TRUE)
    {
        $options = $this->getCountries($active_only);

        return $options->pluck('name', 'id')->all();
    }

    public function getStates($country_id=null, $active_only=TRUE)
    {
        if(!isset($this->_states) || $this->_alwaysRefresh){
            $qb = State::orderBy('name', 'ASC')->orderBy('sort_order', 'ASC');

            if($country_id){
                $qb->where('country_id', $country_id);
            }

            if($active_only){
                $qb->active();
            }

            $this->_states = $qb->get();
        }

        return $this->_states;
    }

    public function getStateOptions($country_id=null, $active_only=TRUE)
    {
        $country = Country::find($country_id);

        if(!$country || !$country->has_descendant){
            return [];
        }

        $options = $this->getStates($country_id, $active_only);

        return $options->pluck('name', 'id')->all();
    }

    public function getCities($state_id=null, $active_only=TRUE)
    {
        if(!isset($this->_cities) || $this->_alwaysRefresh){
            $qb = City::orderBy('name', 'ASC')->orderBy('sort_order', 'ASC');

            if($state_id){
                $qb->where('state_id', $state_id);
            }

            if($active_only){
                $qb->active();
            }

            $this->_cities = $qb->get();
        }

        return $this->_cities;
    }

    public function getCityOptions($state_id=null, $active_only=TRUE)
    {
        $state = State::find($state_id);

        if(!$state || !$state->has_descendant){
            return [];
        }

        $options = $this->getCities($state_id, $active_only);

        return $options->pluck('name', 'id')->all();
    }

    public function getDistricts($city_id=null, $active_only=TRUE)
    {
        if(!isset($this->_districts) || $this->_alwaysRefresh){
            $qb = District::orderBy('name', 'ASC')->orderBy('sort_order', 'ASC');

            if($city_id){
                $qb->where('city_id', $city_id);
            }

            if($active_only){
                $qb->active();
            }

            $this->_districts = $qb->get();
        }

        return $this->_districts;
    }

    public function getDistrictOptions($city_id=null, $active_only=TRUE)
    {
        $city = City::find($city_id);

        if(!$city || !$city->has_descendant){
            return [];
        }

        $options = $this->getDistricts($city_id, $active_only);

        return $options->pluck('name', 'id')->all();
    }

    public function getAreas($district_id=null, $active_only=TRUE)
    {
        if(!isset($this->_areas) || $this->_alwaysRefresh){
            $qb = Area::orderBy('name', 'ASC')->orderBy('sort_order', 'ASC');

            if($district_id){
                $qb->where('district_id', $district_id);
            }

            if($active_only){
                $qb->active();
            }

            $this->_areas = $qb->get();
        }

        return $this->_areas;
    }

    public function getAreaOptions($district_id=null, $active_only=TRUE)
    {
        $district = District::find($district_id);

        if(!$district || !$district->has_descendant){
            return [];
        }

        $options = $this->getAreas($district_id, $active_only);

        return $options->pluck('name', 'id')->all();
    }

    public function printAddress($data)
    {
        $addressLineElements = [];
        $addressElements = [];

        if(!empty($data['address_1'])){
            $addressLineElements[] = $data['address_1'];
        }

        if(!empty($data['address_2'])){
            $addressLineElements[] = $data['address_2'];
        }

        if(!empty($data['area_id'])){
            $area = Area::find($data['area_id']);

            if($area){
                $addressElements[] = $area->name;
            }
        }

        if(!empty($data['district_id'])){
            $district = District::find($data['district_id']);

            if($district){
                $addressElements[] = $district->name;
            }
        }

        if(!empty($data['city_id'])){
            $city = City::find($data['city_id']);

            if($city){
                $addressElements[] = $city->name;
            }
        }

        if(!empty($data['state_id'])){
            $state = State::find($data['state_id']);

            if($state){
                $addressElements[] = $state->name;
            }
        }

        if(!empty($data['country_id'])){
            $country = Country::find($data['country_id']);

            if($country){
                $addressElements[] = $country->name;
            }
        }

        if($addressElements){
            $addressLineElements[] = implode(', ', $addressElements);
        }

        if(!empty($data['postal_code'])){
            $addressLineElements[] = $data['postal_code'];
        }

        return implode('<br/>', $addressLineElements);
    }

    public function setAlwaysRefresh($active)
    {
        $this->_alwaysRefresh = $active;
    }
}