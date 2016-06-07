<?php

namespace Kommercio\Models\Order;

use Illuminate\Database\Eloquent\Model;
use Kommercio\Models\PriceRule\CartPriceRule;
use Kommercio\Models\Product;
use Kommercio\Models\Tax;
use Kommercio\Traits\Model\HasDataColumn;

class LineItem extends Model
{
    use HasDataColumn;

    protected $fillable = ['line_item_id', 'line_item_type', 'name', 'base_price', 'quantity', 'taxable', 'net_price', 'total', 'sort_order', 'data'];
    protected $casts = [
        'taxable' => 'boolean'
    ];

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
        }elseif($data['line_item_type'] == 'shipping'){
            $this->name = $data['name'];
            $this->line_item_id = $data['line_item_id'];
            $this->line_item_type = 'shipping';
            $this->base_price = $data['base_price'];
            $this->net_price = $data['lineitem_total_amount'];
            $this->total = $data['lineitem_total_amount'];
            $this->quantity = 1;
            $this->saveData(['shipping_method' => $data['shipping_method']]);
        }elseif($data['line_item_type'] == 'tax'){
            $this->linkTax($data['tax_id']);
            $this->base_price = $data['lineitem_total_amount'];
            $this->net_price = $data['lineitem_total_amount'];
            $this->total = $data['lineitem_total_amount'];
            $this->quantity = 1;
        }elseif($data['line_item_type'] == 'cart_price_rule'){
            $this->linkCartPriceRule($data['cart_price_rule_id']);
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
        $this->taxable = $product->productDetail->taxable;
        $this->line_item_id = $product->id;
        $this->line_item_type = 'product';
        $this->base_price = $product->getRetailPrice();
    }

    public function linkTax($tax_id)
    {
        $tax = Tax::findOrFail($tax_id);
        $this->name = $tax->getSingleName();
        $this->line_item_id = $tax->id;
        $this->line_item_type = 'tax';
    }

    public function linkCartPriceRule($price_rule_id)
    {
        $priceRule = CartPriceRule::findOrFail($price_rule_id);
        $this->name = $priceRule->name;
        $this->line_item_id = $priceRule->id;
        $this->line_item_type = 'cart_price_rule';
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

    public function getIsShippingAttribute()
    {
        return $this->line_item_type == 'shipping';
    }

    public function getIsTaxAttribute()
    {
        return $this->line_item_type == 'tax';
    }

    public function getIsCartPriceRuleAttribute()
    {
        return $this->line_item_type == 'cart_price_rule';
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

    public function tax()
    {
        return $this->belongsTo('Kommercio\Models\Tax', 'line_item_id');
    }

    public function cartPriceRule()
    {
        return $this->belongsTo('Kommercio\Models\PriceRule\CartPriceRule', 'line_item_id');
    }

    public function shippingMethod()
    {
        return $this->belongsTo('Kommercio\Models\ShippingMethod\ShippingMethod', 'line_item_id');
    }

    //Shipping Specifics
    public function getSelectedMethod()
    {
        $selectedMethod = $this->getData('shipping_method');

        if($selectedMethod){
            $selectedMethod = $this->shippingMethod->getSelectedMethod($selectedMethod);
        }

        return $selectedMethod;
    }
}
