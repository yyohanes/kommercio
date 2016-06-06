<?php

namespace Kommercio\Models;

use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    protected $guarded = ['country', 'states', 'cities', 'districts', 'areas'];
    protected $casts = [
        'active' => 'boolean'
    ];

    //Methods
    public function getSingleName()
    {
        return $this->name.' ('.$this->rate.'%)';
    }

    public function calculateTax($amount)
    {
        return $this->rate/100 * $amount;
    }

    //Scopes
    public function scopeActive($query)
    {
        $query->where('active', 1);
    }

    //Relations
    public function store()
    {
        return $this->belongsTo('Kommercio\Models\Store');
    }

    public function countries()
    {
        return $this->morphedByMany('Kommercio\Models\Address\Country', 'tax_optionable', 'tax_rules');
    }

    public function states()
    {
        return $this->morphedByMany('Kommercio\Models\Address\State', 'tax_optionable', 'tax_rules');
    }

    public function cities()
    {
        return $this->morphedByMany('Kommercio\Models\Address\City', 'tax_optionable', 'tax_rules');
    }

    public function districts()
    {
        return $this->morphedByMany('Kommercio\Models\Address\District', 'tax_optionable', 'tax_rules');
    }

    public function areas()
    {
        return $this->morphedByMany('Kommercio\Models\Address\Area', 'tax_optionable', 'tax_rules');
    }

    //Statics
    public static function getTaxes($options)
    {
        $qb = self::orderBy('sort_order', 'ASC')->active();

        $country = isset($options['country_id'])?:null;
        $state = isset($options['state_id'])?:null;
        $city = isset($options['city_id'])?:null;
        $district = isset($options['district_id'])?:null;
        $area = isset($options['area_id'])?:null;
        $currency = isset($options['currency'])?:null;
        $store = isset($options['store_id'])?:null;

        if($currency){
            $qb->where(function($qb) use ($currency){
                $qb->whereNull('currency')->orWhere('currency', $currency);
            });
        }

        if($store){
            $qb->where(function($qb) use ($store){
                $qb->whereNull('store_id')->orWhere('store_id', $store);
            });
        }

        if($country) {
            $qb->where(function($qb) use ($country){
                $qb->whereDoesntHave('countries')->orWhereHas('countries', function ($query) use ($country) {
                    $query->whereIn('id', [$country]);
                });
            });
        }

        if($state) {
            $qb->where(function($qb) use ($state){
                $qb->whereDoesntHave('states')->orWhereHas('states', function ($query) use ($state) {
                    $query->whereIn('id', [$state]);
                });
            });
        }

        if($city) {
            $qb->where(function($qb) use ($city){
                $qb->whereDoesntHave('cities')->orWhereHas('cities', function ($query) use ($city) {
                    $query->whereIn('id', [$city]);
                });
            });
        }

        if($district) {
            $qb->where(function($qb) use ($district){
                $qb->whereDoesntHave('districts')->orWhereHas('districts', function ($query) use ($district) {
                    $query->whereIn('id', [$district]);
                });
            });
        }

        if($area) {
            $qb->where(function($qb) use ($area){
                $qb->whereDoesntHave('areas')->orWhereHas('areas', function ($query) use ($area) {
                    $query->whereIn('id', [$area]);
                });
            });
        }

        return $qb->get();
    }
}
