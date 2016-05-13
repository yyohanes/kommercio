<?php

namespace Kommercio\Models\ShippingMethod;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    use Translatable;

    public $timestamps = FALSE;

    public $translatedAttributes = ['name','message'];

    protected $fillable = ['name', 'class', 'message', 'sort_order'];
}
