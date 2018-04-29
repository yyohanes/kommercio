<?php

namespace Kommercio\Models\ShippingMethod;

use Illuminate\Database\Eloquent\Model;

class ShippingMethodTranslation extends Model
{
    public $fillable = [
        'name',
        'message',
        'locale',
    ];

    public $timestamps = FALSE;
}
