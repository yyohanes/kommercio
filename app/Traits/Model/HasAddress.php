<?php

namespace Kommercio\Traits\Model;

use Kommercio\Facades\RuntimeCache;
use Kommercio\Models\Address\Area;
use Kommercio\Models\Address\City;
use Kommercio\Models\Address\Country;
use Kommercio\Models\Address\District;
use Kommercio\Models\Address\State;

/**
 * Traits of model with address attributes
 */
trait HasAddress {

    public function getAddress()
    {
        $return = [];
        if($this->address_1){
            $return['address_1'] = $this->address_1;
        }

        if($this->address_1){
            $return['address_2'] = $this->address_2;
        }

        if($this->country_id){
            $return['country_id'] = $this->country_id;
        }

        if($this->state_id){
            $return['state_id'] = $this->state_id;
        }

        if($this->city_id){
            $return['city_id'] = $this->city_id;
        }

        if($this->district_id){
            $return['district_id'] = $this->district_id;
        }

        if($this->area_id){
            $return['area_id'] = $this->area_id;
        }

        if($this->postal_code){
            $return['postal_code'] = $this->postal_code;
        }

        if($this->custom_city){
            $return['custom_city'] = $this->custom_city;
        }

        return $return;
    }

    //Accessors
    public function getCountryAttribute()
    {
        if($this->country_id){
            return RuntimeCache::getOrSet('country_'.$this->country_id, function(){
                return Country::find($this->country_id);
            });
        }

        return null;
    }

    public function getStateAttribute()
    {
        if($this->state_id){
            return RuntimeCache::getOrSet('state_'.$this->state_id, function(){
                return State::find($this->state_id);
            });
        }

        return null;
    }

    public function getCityAttribute()
    {
        if($this->city_id){
            return RuntimeCache::getOrSet('city_'.$this->city_id, function(){
                return City::find($this->city_id);
            });
        }

        return null;
    }

    public function getDistrictAttribute()
    {
        if($this->district_id){
            return RuntimeCache::getOrSet('district_'.$this->district_id, function(){
                return District::find($this->district_id);
            });
        }

        return null;
    }

    public function getAreaAttribute()
    {
        if($this->area_id){
            return RuntimeCache::getOrSet('area_'.$this->area_id, function(){
                return Area::find($this->area_id);
            });
        }

        return null;
    }

    public function getLowestAddressAttribute()
    {
        $search = ['area_id', 'district_id', 'city_id', 'state_id', 'country_id'];

        foreach ($search as $key) {
            if (!empty($this->$key)) {
                $addressType = str_replace('_id', '', $key);
                return $this->$addressType;
            }
        }
    }
}
