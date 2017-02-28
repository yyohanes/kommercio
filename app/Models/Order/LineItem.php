<?php

namespace Kommercio\Models\Order;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Kommercio\Facades\OrderHelper;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\File;
use Kommercio\Models\PriceRule\CartPriceRule;
use Kommercio\Models\PriceRule\Coupon;
use Kommercio\Models\Product;
use Kommercio\Models\ProductDetail;
use Kommercio\Models\Tax;
use Kommercio\Traits\Model\HasDataColumn;
use Kommercio\Traits\Model\MediaAttachable;

class LineItem extends Model
{
    use HasDataColumn, MediaAttachable;

    protected $fillable = ['line_item_id', 'line_item_type', 'name', 'base_price', 'quantity', 'taxable', 'notes', 'net_price', 'discount_total', 'tax_total', 'total', 'sort_order', 'data', 'temporary', 'product_composite_id', 'parent_id'];
    protected $casts = [
        'taxable' => 'boolean',
        'temporary' => 'boolean'
    ];

    private $_compositeConfiguration;

    //Override
    public function save(array $options = [])
    {
        $saved = parent::save($options);

        foreach($this->children as $child){
            $child->parent()->associate($this);
            $child->save();
        }

        return $saved;
    }

    //Methods
    public function getPrintName()
    {
        $name = $this->name;

        if($this->isCoupon){
            $name = 'Coupon ('.$this->coupon->coupon_code.')';
        }

        return $name;
    }

    public function calculateNet($withTax = true, $includeChildren = true)
    {
        $total = $this->net_price + $this->discount_total;

        if($withTax){
            $total += $this->tax_total;
        }

        if($includeChildren){
            foreach($this->children as $child){
                $total += $child->calculateTotal($withTax);
            }
        }

        return round($total, config('project.line_item_total_precision'));
    }

    public function calculateSubNet($includeChildren = true)
    {
        $rate = $this->taxable?$this->tax_rate:0;

        $total = $this->net_price + $this->net_price * $rate/100;

        if($includeChildren){
            foreach($this->children as $child){
                $total += $child->calculateTotal();
            }
        }

        return round($total, config('project.line_item_total_precision'));
    }

    public function calculateTotal($withTax = true)
    {
        $this->total = round($this->calculateNet($withTax) * $this->quantity, config('project.line_item_total_precision'));

        return $this->total;
    }

    public function calculateSubtotal($includeChildren = true)
    {
        $net = $this->net_price;

        if($includeChildren){
            foreach($this->children as $child){
                $net += ($child->net_price * $child->quantity);
            }
        }

        return round($net * $this->quantity, config('project.line_item_total_precision'));
    }

    public function calculateSubtotalWithTax($includeChildren = true)
    {
        return round($this->calculateSubNet($includeChildren) * $this->quantity, config('project.line_item_total_precision'));
    }

    public function calculateMargin()
    {
        return round(($this->base_price - $this->net_price) * $this->quantity, config('project.line_item_total_precision'));
    }

    public function processData($data, $sort_order = 0)
    {
        //Set defaults
        if(!isset($data['net_price'])){
            $data['net_price'] = 0;
        }

        if(!isset($data['quantity'])){
            $data['quantity'] = 1;
        }

        //Process
        if($data['line_item_type'] == 'product'){
            if(!empty($data['sku'])){
                $this->linkProductBySKU($data['sku']);
            }elseif(!empty($data['line_item_id'])){
                $this->linkProductById($data['line_item_id']);
            }elseif(!empty($data['product'])){
                $this->linkProduct($data['product']);
            }
            $this->net_price = $data['net_price'];
            $this->quantity = $data['quantity'];

            $this->calculateTotal();
        }elseif($data['line_item_type'] == 'fee'){
            $this->name = $data['name'];
            $this->line_item_type = 'fee';
            $this->base_price = $data['net_price'];
            $this->net_price = $data['net_price'];
            $this->total = $data['lineitem_total_amount'];
            $this->quantity = 1;
        }elseif($data['line_item_type'] == 'shipping'){
            $this->name = $data['name'];
            $this->line_item_id = $data['line_item_id'];
            $this->line_item_type = 'shipping';
            $this->base_price = $data['net_price'];
            $this->net_price = $data['net_price'];
            $this->total = $data['lineitem_total_amount'];
            $this->taxable = $data['taxable'];
            $this->quantity = 1;
            $this->saveData(['shipping_method' => $data['shipping_method']]);
        }elseif($data['line_item_type'] == 'tax'){
            $this->linkTax($data['tax_id']);
            $this->base_price = $data['base_price'];
            $this->net_price = $data['lineitem_total_amount'];
            $this->total = $data['lineitem_total_amount'];
            $this->quantity = 1;
        }elseif($data['line_item_type'] == 'cart_price_rule'){
            $this->linkCartPriceRule($data['cart_price_rule_id']);
            $this->base_price = $data['lineitem_total_amount'];
            $this->net_price = $data['lineitem_total_amount'];
            $this->total = $data['lineitem_total_amount'];
            $this->quantity = 1;
        }elseif($data['line_item_type'] == 'coupon') {
            $coupon = Coupon::findOrFail($data['coupon_id']);
            $this->name = $coupon->cartPriceRule->name;
            $this->line_item_id = $coupon->id;
            $this->line_item_type = 'coupon';
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

        if(isset($data['notes'])){
            $this->notes = $data['notes'];
        }

        if(isset($data['product_composite_id'])){
            $this->productComposite()->associate($data['product_composite_id']);

            if($this->productComposite->free){
                $this->net_price = 0;
            }
        }

        if(is_null($this->taxable)){
            $this->taxable = false;
        }

        if(is_null($this->temporary)){
            $this->temporary = false;
        }

        $this->sort_order = $sort_order;

        if(!empty($data['configurations'])){
            $this->save();
            $this->productConfigurations()->sync($data['configurations']);
        }

        if(!empty($data['children'])){
            $this->processChildren($data['children']);
        }
    }

    public function clearData()
    {
        //Delete attachment file if any
        foreach($this->getData('attachments', []) as $attachmendId){
            $file = File::find($attachmendId);
            if($file){
                $file->delete();
            }
        }

        foreach($this->fillable as $fillableAttribute){
            $this->setAttribute($fillableAttribute, NULL);
        }

        //Clear attached configurations
        DB::table($this->productConfigurations()->getTable())->where('line_item_id', $this->id)->delete();
    }

    public function linkProductBySKU($sku)
    {
        $product = Product::where('sku', $sku)->firstOrFail();
        $this->linkProduct($product);
    }

    public function linkProductById($id)
    {
        $product = Product::findOrFail($id);
        $this->linkProduct($product);
    }

    public function linkProduct($product)
    {
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
        $this->tax_rate = $tax->rate;
        $this->line_item_id = $tax->id;
        $this->line_item_type = 'tax';
    }

    public function linkCartPriceRule($price_rule_id)
    {
        $priceRule = CartPriceRule::findOrFail($price_rule_id);
        $this->name = $priceRule->name;
        $this->line_item_id = $priceRule->id;
        $this->line_item_type = 'cart_price_rule';

        if($priceRule->isFreeShipping){
            $this->line_item_type = 'free_shipping';
        }
    }

    public function getAttachments()
    {
        $attachments = [];

        foreach($this->getData('attachments', []) as $attachment){
            if($attachment = File::find($attachment)){
                $attachments[] = $attachment;
            }
        }

        return $attachments;
    }

    public function getCompositeConfiguration()
    {
        if(!isset($this->_compositeConfiguration)){
            $product = $this->parent->product->isVariation?$this->parent->product->parent:$this->parent->product;
            $this->_compositeConfiguration = $product->getCompositeConfiguration($this->product_composite_id);
        }

        return $this->_compositeConfiguration;
    }

    public function getChildrenByComposite($composite)
    {
        if(is_object($composite)){
            $composite = $composite->id;
        }

        return $this->children->where('product_composite_id', $composite);
    }

    public function getChildrenByProduct($product)
    {
        if(is_object($product)){
            $product_id = $product->id;
        }elseif(is_string($product)){
            $product = Product::where('sku', $product)->first();

            if(!$product){
                return null;
            }

            $product_id = $product->id;
        }else{
            $product_id = $product;
        }

        return $this->children->where('line_item_id', $product_id)->where('line_item_type', 'product');
    }

    public function processChildren($children)
    {
        $childrenData = [];

        if($this->isProduct && $this->product->composites->count() > 0){
            foreach($children as $compositeId => $compositeData){
                foreach($compositeData as $datumKey => $compositeDatum){
                    if(!empty($compositeDatum['line_item_id'])){
                        if(isset($compositeDatum['net_price'])){
                            $compositeDatum['net_price'] = floatval($compositeDatum['net_price']);
                        }
                        $childrenData[] = $compositeDatum;
                    }
                }
            }

            foreach($this->product->composites as $composite){
                if(!isset($children[$composite->id]) && $composite->isSingle){
                    $childrenData[] = [
                        'product' => $composite->product,
                        'quantity' => $composite->minimum,
                        'line_item_type' => 'product',
                        'product_composite_id' => $composite->id
                    ];
                }
            }
        }

        $existingLineItems = $this->children;

        $count = count($existingLineItems);

        $lineItems = [];

        foreach($childrenData as $child){
            $lineItem = OrderHelper::reuseOrCreateLineItem($this->order, $existingLineItems, $count);
            $lineItem->processData($child, $count);
            $lineItems[] = $lineItem;
            $count += 1;
        }

        //Delete unused line items
        foreach($existingLineItems as $existingLineItem){
            $existingLineItem->delete();
        }

        $this->setRelation('children', $lineItems);
    }

    //Accessors
    public function getDiscountApplicableAttribute()
    {
        return $this->isProduct || $this->isFee || $this->isShipping;
    }

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

    public function getIsFreeShippingAttribute()
    {
        $isFreeShipping = $this->line_item_type == 'free_shipping';

        if(!$isFreeShipping && $this->isCoupon){
            $isFreeShipping = $this->coupon->cartPriceRule->offer_type == CartPriceRule::OFFER_TYPE_FREE_SHIPPING;
        }

        return $isFreeShipping;
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
    public function parent()
    {
        return $this->belongsTo('Kommercio\Models\Order\LineItem', 'parent_id');
    }

    public function children()
    {
        return $this->hasMany('Kommercio\Models\Order\LineItem', 'parent_id')->orderBy('sort_order', 'ASC');
    }

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

    public function coupon()
    {
        return $this->belongsTo('Kommercio\Models\PriceRule\Coupon', 'line_item_id')->with('cartPriceRule');
    }

    public function shippingMethod()
    {
        return $this->belongsTo('Kommercio\Models\ShippingMethod\ShippingMethod', 'line_item_id');
    }

    public function productComposite()
    {
        return $this->belongsTo('Kommercio\Models\Product\Composite\ProductComposite');
    }

    public function productConfigurations()
    {
        return $this->belongsToMany('Kommercio\Models\Product\Configuration\ProductConfiguration')->withPivot(['type', 'label', 'value']);
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
