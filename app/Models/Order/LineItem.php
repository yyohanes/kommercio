<?php

namespace Kommercio\Models\Order;

use Illuminate\Database\Eloquent\Model;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\PriceRule\CartPriceRule;
use Kommercio\Models\Product;
use Kommercio\Models\ProductDetail;
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
    public function getPrintName()
    {
        $name = $this->name;

        if($this->isCoupon){
            $name = 'Coupon ('.$this->getData('coupon_code').')';
        }

        return $name;
    }

    public function calculateTotal()
    {
        $this->total = round($this->net_price * $this->quantity, config('project.line_item_total_precision'));

        return $this->total;
    }

    public function calculateSubtotal()
    {
        return round($this->base_price * $this->quantity, config('project.line_item_total_precision'));
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
            $this->taxable = $data['taxable'];
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
        }elseif($data['line_item_type'] == 'rounding'){
            $this->name = 'Rounding';
            $this->line_item_type = 'rounding';
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
        $this->line_item_type = $priceRule->isCoupon?'coupon':'cart_price_rule';

        if($priceRule->isCoupon){
            $this->saveData(['coupon_code' => $priceRule->coupon_code]);
        }
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

    public function getIsRoundingAttribute()
    {
        return $this->line_item_type == 'rounding';
    }

    public function getIsCartPriceRuleAttribute()
    {
        return $this->line_item_type == 'cart_price_rule';
    }

    public function getIsCouponAttribute()
    {
        return $this->line_item_type == 'coupon';
    }

    public function getQuantityAttribute()
    {
        return $this->attributes['quantity'] + 0.00;
    }

    //Scopes
    public function scopeIsProduct($query, $product_id)
    {
        $query->where('line_item_id', $product_id)->where('line_item_type', 'product');
    }

    public function scopeLineItemType($query, $type)
    {
        $query->where('line_item_type', $type);
    }

    public function scopeJoinProduct($query)
    {
        $productTable = with(new Product())->getTable();
        $productDetailTable = with(new ProductDetail())->getTable();

        $query->leftJoin($productTable.' AS P', 'P.id', '=', 'line_item_id');
        $query->leftJoin($productDetailTable.' AS PD', function($join){
            $join->on('PD.product_id', '=', 'line_item_id')
                ->where('PD.store_id', '=', ProjectHelper::getActiveStore()->id);
        });
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
    public function getSelectedMethod($key=null)
    {
        $selectedMethod = $this->getData('shipping_method');

        if($selectedMethod){
            $selectedMethod = $this->shippingMethod->getSelectedMethod($selectedMethod);
        }

        if($key){
            return $selectedMethod[$key];
        }

        return $selectedMethod;
    }
}
