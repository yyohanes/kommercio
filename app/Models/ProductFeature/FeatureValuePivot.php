<?php

namespace Kommercio\Models\ProductFeature;

use Illuminate\Database\Eloquent\Relations\Pivot;

class FeatureValuePivot extends Pivot
{
    public function productFeature()
    {
        return $this->belongsTo('Kommercio\Models\ProductFeature\ProductFeature');
    }

    public function productFeatureValue()
    {
        return $this->belongsTo('Kommercio\Models\ProductFeature\ProductFeatureValue');
    }
}