<?php

namespace Kommercio\Models\Customer;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class CustomerGroup extends Model
{
    use Translatable;

    public $translatedAttributes = ['name'];
    protected $fillable = ['name', 'sort_order'];

    //Accessors
    public function getCustomerCountAttribute()
    {
        return $this->customers->count();
    }

    //Relations
    public function customers()
    {
        return $this->belongsToMany('Kommercio\Models\Customer')->withTimestamps();
    }

    //Statics
    public static function getCustomerGroupOptions()
    {
        $customerGroups = self::orderBy('sort_order', 'ASC')->get();

        $return = [];

        foreach($customerGroups as $customerGroup){
            $return[$customerGroup->id] = $customerGroup->name;
        }

        return $return;
    }
}
