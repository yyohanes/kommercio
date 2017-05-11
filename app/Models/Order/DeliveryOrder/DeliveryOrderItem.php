<?php

namespace Kommercio\Models\Order\DeliveryOrder;

use Illuminate\Database\Eloquent\Model;
use Kommercio\Models\Order\LineItem;
use Kommercio\Models\Product;
use Kommercio\Traits\Model\HasDataColumn;

class DeliveryOrderItem extends Model
{
    use HasDataColumn;

    public $timestamps = false;
    protected $fillable = ['name', 'quantity', 'price', 'weight', 'data', 'sort_order'];

    // Relations
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function lineItem()
    {
        return $this->belongsTo(LineItem::class);
    }

    public function deliveryOrder()
    {
        return $this->belongsTo(DeliveryOrder::class);
    }
}
