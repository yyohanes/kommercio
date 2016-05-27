<?php

namespace Kommercio\Models\Order;

use Illuminate\Database\Eloquent\Model;
use Kommercio\Models\Product;

class LineItem extends Model
{
    protected $fillable = ['line_item_id', 'line_item_type', 'name', 'base_price', 'quantity', 'net_price', 'total', 'sort_order'];

    //Methods
    public function calculateTotal()
    {
        $this->total = $this->net_price * $this->quantity;

        return $this->total;
    }

    public function calculateSubtotal()
    {
        return $this->base_price * $this->quantity;
    }

    public function processData($data, $sort_order = 0)
    {
        if($data['line_item_type'] == 'product'){
            $this->linkProductBySKU($data['sku']);
            $this->net_price = $data['net_price'];
            $this->quantity = $data['quantity'];

            $this->calculateTotal();
        }elseif($data['line_item_type'] == 'fee'){
            $this->name = $data['name'];
            $this->line_item_type = 'fee';
            $this->base_price = $data['lineitem_total_amount'];
            $this->net_price = $data['lineitem_total_amount'];
            $this->total = $data['lineitem_total_amount'];
            $this->quantity = 1;
        }

        $this->sort_order = $sort_order;
    }

    public function clearData()
    {
        foreach($this->fillable as $fillableAttribute){
            $this->setAttribute($fillableAttribute, NULL);
        }
    }

    public function linkProductBySKU($sku)
    {
        $product = Product::where('sku', $sku)->firstOrFail();
        $this->name = $product->name;
        $this->line_item_id = $product->id;
        $this->line_item_type = 'product';
        $this->base_price = $product->getRetailPrice();
    }

    //Accessors
    public function getIsProductAttribute()
    {
        return $this->line_item_type == 'product';
    }

    public function getIsFeeAttribute()
    {
        return $this->line_item_type == 'fee';
    }

    public function getQuantityAttribute()
    {
        return $this->attributes['quantity'] + 0.00;
    }

    //Relations
    public function order()
    {
        return $this->belongsTo('Kommercio\Models\Order\Order');
    }

    public function product()
    {
        return $this->belongsTo('Kommercio\Models\Product', 'line_item_id');
    }
}
