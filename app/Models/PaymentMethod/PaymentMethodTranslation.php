<?php

namespace Kommercio\Models\PaymentMethod;

use Illuminate\Database\Eloquent\Model;

class PaymentMethodTranslation extends Model
{
    public $fillable = [
        'name',
        'message',
        'locale',
    ];

    public $timestamps = FALSE;
}
