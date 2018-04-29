<?php

namespace Kommercio\Models\Product\Configuration;

use Illuminate\Database\Eloquent\Model;

class ProductConfigurationTranslation extends Model
{
    public $fillable = [
        'name',
        'locale',
    ];

    public $timestamps = false;
}
