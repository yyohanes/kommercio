<?php

namespace Kommercio\Models\PaymentMethod;

use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use Translatable;

    public $timestamps = FALSE;
    public $translatedAttributes = ['name', 'message'];

    protected $fillable = ['name', 'class', 'message', 'sort_order'];
}
