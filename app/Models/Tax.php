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

        $country = !empty($options['country_id'])?$options['country_id']:null;
        $state = !empty($options['state_id'])?$options['state_id']:null;
        $city = !empty($options['city_id'])?$options['city_id']:null;
        $district = !empty($options['district_id'])?$options['district_id']:null;
        $area = !empty($options['area_id'])?$options['area_id']:null;
        $currency = !empty($options['currency'])?$options['currency']:null;
        $store = !empty($options['store_id'])?$options['store_id']:null;

        $qb->where(function($qb) use ($currency){
            $qb->whereNull('currency');

            if($currency){
                $qb->orWhere('currency', $currency);
            }
        });

        $qb->where(function($qb) use ($store){
            $qb->whereNull('store_id');

            if($store){
                $qb->orWhere('store_id', $store);
            }
        });

        $qb->where(function($qb) use ($country){
            $qb->whereDoesntHave('countries');

            if($country) {
                $qb->orWhereHas('countries', function ($query) use ($country) {
                    $query->whereIn('id', [$country]);
                });
            }
        });

        $qb->where(function($qb) use ($state){
            $qb->whereDoesntHave('states');

            if($state) {
                $qb->orWhereHas('states', function ($query) use ($state) {
                    $query->whereIn('id', [$state]);
                });
            }
        });

        $qb->where(function($qb) use ($city){
            $qb->whereDoesntHave('cities');

            if($city) {
                $qb->orWhereHas('cities', function ($query) use ($city) {
                    $query->whereIn('id', [$city]);
                });
            }
        });

        $qb->where(function($qb) use ($district){
            $qb->whereDoesntHave('districts');

            if($district) {
                $qb->orWhereHas('districts', function ($query) use ($district) {
                    $query->whereIn('id', [$district]);
                });
            }
        });

        $qb->where(function($qb) use ($area){
            $qb->whereDoesntHave('areas');

            if($area) {
                $qb->orWhereHas('areas', function ($query) use ($area) {
                    $query->whereIn('id', [$area]);
                });
            }
        });

        return $qb->get();
    }
}
