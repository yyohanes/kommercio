<?php

namespace Kommercio\Models\Product\Composite;

use Illuminate\Database\Eloquent\Model;

class ProductCompositeTranslation extends Model
{
    public $fillable = [
        'label',
        'locale',
    ];
}
