<?php

namespace Kommercio\Models\ProductAttribute;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AttributeValuePivot extends Pivot
{
    public function productAttribute()
    {
        return $this->belongsTo('Kommercio\Models\ProductAttribute\ProductAttribute');
    }

    public function productAttributeValue()
    {
        return $this->belongsTo('Kommercio\Models\ProductAttribute\ProductAttributeValue');
    }
}