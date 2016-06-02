<?php

namespace Kommercio\Models;

use Illuminate\Database\Eloquent\Model;

class Tax extends Model
{
    protected $guarded = ['country', 'states', 'cities', 'districts', 'areas'];
    protected $casts = [
        'active' => 'boolean'
    ];

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
}
