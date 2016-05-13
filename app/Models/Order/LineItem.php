<?php

namespace Kommercio\Models\Order;

use Illuminate\Database\Eloquent\Model;

class LineItem extends Model
{
    protected $guarded = [];

    //Relations
    public function order()
    {
        return $this->belongsTo('Kommercio\Models\Order\Order');
    }
}
