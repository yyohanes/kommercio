<?php

namespace Kommercio\Models\Order;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Kommercio\Facades\CurrencyHelper;
use Kommercio\Facades\PriceFormatter;
use Kommercio\Models\Interfaces\AuthorSignatureInterface;
use Kommercio\Models\PriceRule\CartPriceRule;
use Kommercio\Models\Product;
use Kommercio\Models\Profile\Profile;
use Kommercio\Models\ShippingMethod\ShippingMethod;
use Kommercio\Models\Tax;
use Kommercio\Traits\Model\AuthorSignature;
use Kommercio\Traits\Model\HasDataColumn;
use Kommercio\Models\Order\Payment;

class Order extends Model implements AuthorSignatureInterface
{
    use SoftDeletes, AuthorSignature, HasDataColumn;

    const STATUS_CANCELLED = 'cancelled';
    const STATUS_ADMIN_CART = 'admin_cart';
    const STATUS_CART = 'cart';
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_COMPLETED = 'completed';

    public static $processedStatus = [self::STATUS_PENDING, self::STATUS_PROCESSING];

    public $referenceFormat;

    protected $guarded = [];
    protected $dates = ['deleted_at', 'delivery_date', 'checkout_at'];

    public function __construct($attributes = [])
    {
        $this->referenceFormat = config('project.order_number_format');

        parent::__construct($attributes);
    }

    //Relations
    public function lineItems()
    {
        return $this->hasMany('Kommercio\Models\Order\LineItem')->orderBy('sort_order', 'ASC');
    }

    public function customer()
    {
        return $this->belongsTo('Kommercio\Models\Customer');
    }

    public function store()
    {
        return $this->belongsTo('Kommercio\Models\Store');
    }

    public function billingProfile()
    {
        return $this->belongsTo('Kommercio\Models\Profile\Profile', 'billing_profile_id');
    }

    public function shippingProfile()
    {
        return $this->belongsTo('Kommercio\Models\Profile\Profile', 'shipping_profile_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo('Kommercio\Models\PaymentMethod\PaymentMethod');
    }

    public function shippingMethod()
    {
        return $this->belongsTo('Kommercio\Models\ShippingMethod\ShippingMethod');
    }

    public function payments()
    {
        return $this->hasMany('Kommercio\Models\Order\Payment');
    }

    public function comments()
    {
        return $this->hasMany('Kommercio\Models\Order\OrderComment');
    }

    public function internalMemos()
    {
        return $this->comments()->internalMemo()->orderBy('created_at', 'DESC');
    }

    //Methods
    public function reset()
    {
        if(in_array($this->status, [self::STATUS_CART, self::STATUS_ADMIN_CART])){
            $this->lineItems()->delete();

            $this->delivery_date = null;
            $this->store_id = null;
            $this->payment_method_id = null;
            $this->unsetData('checkout_step');

            $time = $this->freshTimestamp();
            $this->setCreatedAt($time);
            $this->setUpdatedAt($time);

            $this->save();
        }
    }

    public function addToCart(Product $product, $quantity = 1, $options = [])
    {
        $existingLineItems = $this->getProductLineItems();

        //if already exists
        $alreadyExist = FALSE;
        foreach($existingLineItems as $existingLineItem){
            if($existingLineItem->line_item_id == $product->id){
                $alreadyExist = TRUE;
                $existingLineItem->quantity += $quantity;
                $existingLineItem->calculateTotal();
                $existingLineItem->save();
                break;
            }
        }

        if(!$alreadyExist){
            $lineItem = new LineItem();
            $lineItem->order()->associate($this);
            $lineItem->processData([
                'line_item_type' => 'product',
                'net_price' => $product->getNetPrice(),
                'quantity' => $quantity,
                'sku' => $product->sku
            ]);
            $lineItem->save();
        }

        $this->load('lineItems');

        return $this;
    }

    public function removeFromCart(Product $product)
    {
        $existingLineItems = $this->getProductLineItems();

        foreach($existingLineItems as $existingLineItem){
            if($existingLineItem->line_item_id == $product->id){
                $existingLineItem->delete();
            }
        }

        $this->load('lineItems');

        return $this;
    }

    public function updateQuantity(Product $product, $quantity = 1)
    {
        $existingLineItems = $this->getProductLineItems();

        //if already exists
        $alreadyExist = FALSE;
        foreach($existingLineItems as $existingLineItem){
            if($existingLineItem->line_item_id == $product->id){
                $alreadyExist = TRUE;

                if($quantity){
                    $existingLineItem->quantity = $quantity;
                    $existingLineItem->calculateTotal();
                    $existingLineItem->save();
                }else{
                    $existingLineItem->delete();
                }

                break;
            }
        }

        if(!$alreadyExist){
            $lineItem = new LineItem();
            $lineItem->order()->associate($this);
            $lineItem->processData([
                'line_item_type' => 'product',
                'net_price' => $product->getNetPrice(),
                'quantity' => $quantity,
                'sku' => $product->sku
            ]);
            $lineItem->save();
        }

        $this->load('lineItems');

        return $this;
    }

    public function addCoupon(CartPriceRule $coupon)
    {
        $existingLineItems = $this->getCouponLineItems();

        //if already exists
        $alreadyExist = FALSE;
        foreach($existingLineItems as $existingLineItem){
            if($existingLineItem->line_item_id == $coupon->id){
                $alreadyExist = TRUE;
                break;
            }
        }

        if(!$alreadyExist){
            $lineItem = new LineItem();
            $lineItem->order()->associate($this);
            $lineItem->processData([
                'line_item_type' => 'cart_price_rule',
                'cart_price_rule_id' => $coupon->id,
                'lineitem_total_amount' => 0, //This is purposely set to 0 because it's not possible to calculate now. Calculation will be done later at Controller level
            ]);
            $lineItem->save();

            $this->load('lineItems');
        }

        return $this;
    }

    public function removeCoupon(CartPriceRule $coupon)
    {
        $existingLineItems = $this->getCouponLineItems();

        foreach($existingLineItems as $existingLineItem){
            if($existingLineItem->line_item_id == $coupon->id){
                $existingLineItem->delete();
                break;
            }
        }

        $this->load('lineItems');

        return $this;
    }

    public function updateShippingMethod($selected_method, $selected_method_data = null)
    {
        $existingLineItems = $this->getShippingLineItems();

        //if already exists
        $alreadyExist = FALSE;
        foreach($existingLineItems as $existingLineItem){
            if($existingLineItem->getData('shipping_method') == $selected_method){
                $alreadyExist = TRUE;
                $existingLineItem->clearData();
                $lineItem = $existingLineItem;
            }else{
                $existingLineItem->delete();
            }
        }

        if(!$alreadyExist){
            $lineItem = new LineItem();
            $lineItem->order()->associate($this);
        }

        //If $selected_method_data empty, then calculate
        if(!$selected_method_data){
            //Get all methods first than filter
            $shippingOptions = ShippingMethod::getShippingMethods([
                'order' => $this
            ]);

            if(!empty($shippingOptions)){
                foreach($shippingOptions as $selectedMethod => $shippingOption){
                    if($selectedMethod == $selected_method){
                        $shipping_method = ShippingMethod::findOrFail($shippingOption['shipping_method_id']);
                        $price = CurrencyHelper::convert($shippingOption['price']['amount'], $shippingOption['price']['currency']);
                        break;
                    }
                }
            }
        }else{
            $shippingOption = $selected_method_data;
            $shipping_method = ShippingMethod::findOrFail($selected_method_data['shipping_method_id']);
            $price = CurrencyHelper::convert($selected_method_data['price']['amount'], $selected_method_data['price']['currency']);
        }

        if(isset($shipping_method)){
            $lineItem->processData([
                'line_item_type' => 'shipping',
                'name' => $shippingOption['name'],
                'line_item_id' => $shipping_method->id,
                'taxable' => $shipping_method->taxable,
                'shipping_method' => $selected_method,
                'base_price' => $price,
                'net_price' => $price,
                'lineitem_total_amount' => $price, //This is purposely set to default because it's not possible to calculate now. Calculation will be done later at Controller level
            ]);
            $lineItem->calculateTotal();
            $lineItem->save();
        }else{
            $lineItem->delete();
        }

        $this->load('lineItems');

        return $this;
    }

    public function generateReference()
    {
        $format = $this->referenceFormat;
        $formatElements = explode(':', $format);

        $counterLength = config('project.order_number_counter_length');

        $lastOrder = self::checkout()
            ->whereRaw("DATE_FORMAT(checkout_at, '%d-%m-%Y') = ?", [$this->checkout_at->format('d-m-Y')])
            ->where('store_id', $this->store_id)
            ->orderBy(DB::raw('CAST(order_number as UNSIGNED)'), 'DESC')
            ->first();
        $totalCheckedOutOrder = $lastOrder?intval($lastOrder->order_number):0;
        $this->order_number = str_pad($totalCheckedOutOrder + 1, $counterLength, 0, STR_PAD_LEFT);

        $orderReference = '';
        foreach($formatElements as $formatElement){
            switch($formatElement){
                case 'store_code':
                    $store = $this->store;
                    if($store){
                        $orderReference .= $store->code;
                    }
                    break;
                case 'order_year':
                    $orderReference .= $this->checkout_at->format('y');
                    break;
                case 'order_month':
                    $orderReference .= $this->checkout_at->format('m');
                    break;
                case 'order_day':
                    $orderReference .= $this->checkout_at->format('d');
                    break;
                case 'counter':
                    $orderReference .= $this->order_number;
                    break;
                default:
                    break;
            }
        }

        $this->reference = $orderReference;
        return $orderReference;
    }

    public function processStocks()
    {
        foreach($this->lineItems as $lineItem){
            if($lineItem->isProduct){
                $lineItem->product->reduceStock($lineItem->quantity);
            }
        }
    }

    public function returnStocks()
    {
        foreach($this->lineItems as $lineItem){
            if($lineItem->isProduct){
                $lineItem->product->increaseStock($lineItem->quantity);
            }
        }
    }

    public function getTaxes()
    {
        $qb = Tax::orderBy('sort_order', 'ASC')->active();

        $profile = $this->billingProfile->fillDetails();

        $taxes = Tax::getTaxes([
            'country_id' => $profile->country_id,
            'state_id' => $profile->state_id,
            'city_id' => $profile->city_id,
            'district_id' => $profile->district_id,
            'area_id' => $profile->area_id,
            'currency' => $this->currency,
            'store_id' => $this->store_id,
        ]);

        return $taxes;
    }

    public function saveProfile($type, $data)
    {
        if($type == 'billing'){
            $profileRelation = 'billingProfile';
            $profile = $this->billingProfile;
        }else{
            $profileRelation = 'shippingProfile';
            $profile = $this->shippingProfile;
        }

        if(!$profile){
            $profile = new Profile();
            $profile->profileable()->associate($this);
            $profile->save();

            $this->$profileRelation()->associate($profile);
            $this->save();
        }

        $profile->saveDetails($data);

        $this->load($profileRelation);
    }

    public function calculateSubtotal()
    {
        $this->subtotal = $this->calculateProductTotal();
        $this->subtotal = PriceFormatter::round($this->subtotal);

        return $this->subtotal;
    }

    public function calculateShippingTotal($total = false, $withTax = false)
    {
        $this->shipping_total = 0;

        foreach($this->lineItems as $lineItem){
            if($lineItem->isShipping){
                if($total){
                    $this->shipping_total += $lineItem->calculateTotal();
                }else{
                    if($withTax){
                        $this->shipping_total += $lineItem->calculateSubtotalWithTax();
                    }else{
                        $this->shipping_total += $lineItem->calculateSubtotal();
                    }
                }
            }
        }

        $this->shipping_total = PriceFormatter::round($this->shipping_total);

        return $this->shipping_total;
    }

    public function calculateDiscountTotal()
    {
        $this->discount_total = 0;

        foreach($this->getCartPriceRuleLineItems() as $cartPriceRuleLineItem){
            $this->discount_total += $cartPriceRuleLineItem->calculateTotal();
        }

        foreach($this->getCouponLineItems() as $couponLineItem){
            $this->discount_total += $couponLineItem->calculateTotal();
        }

        $this->discount_total = PriceFormatter::round($this->discount_total);

        return $this->discount_total;
    }

    public function calculateTaxTotal()
    {
        $this->tax_total = 0;

        foreach($this->getTaxLineItems() as $taxLineItem){
            $this->tax_total += $taxLineItem->calculateTotal();
        }

        $this->tax_total = PriceFormatter::round($this->tax_total);

        return $this->tax_total;
    }

    public function calculateAdditionalTotal($total = false, $withTax = false)
    {
        $this->additional_total = 0;

        foreach($this->lineItems as $lineItem){
            if($lineItem->isFee){
                if($total){
                    $this->additional_total += $lineItem->calculateTotal();
                }else{
                    if($withTax){
                        $this->additional_total += $lineItem->calculateSubtotalWithTax();
                    }else{
                        $this->additional_total += $lineItem->calculateSubtotal();
                    }
                }
            }
        }

        $this->additional_total = PriceFormatter::round($this->additional_total);

        return $this->additional_total;
    }

    public function calculateTotal()
    {
        $subtotal = $this->calculateSubtotal();
        $shippingTotal = $this->calculateShippingTotal();
        $discountTotal = $this->calculateDiscountTotal();
        $additionalTotal = $this->calculateAdditionalTotal();
        $taxTotal = $this->calculateTaxTotal();

        $this->total = PriceFormatter::round($subtotal + $shippingTotal + $discountTotal + $additionalTotal + $taxTotal);
        $beforeRoundingTotal = $this->total;

        $this->total = PriceFormatter::round($this->total, config('project.total_precision'), config('project.total_rounding'));

        $this->rounding_total = PriceFormatter::calculateRounding($beforeRoundingTotal, $this->total);

        return $this->total;
    }

    public function calculateProductTotal($total = false, $withTax = false)
    {
        $productTotal = 0;

        foreach($this->lineItems as $lineItem){
            if($lineItem->isProduct){
                if($total){
                    $productTotal += $lineItem->calculateTotal();
                }else{
                    if($withTax){
                        $productTotal += $lineItem->calculateSubtotalWithTax();
                    }else{
                        $productTotal += $lineItem->calculateSubtotal();
                    }
                }
            }
        }

        $productTotal = PriceFormatter::round($productTotal);

        return $productTotal;
    }

    public function calculateQuantityTotal()
    {
        $quantityTotal = 0;

        foreach($this->lineItems as $lineItem){
            if($lineItem->isProduct){
                $quantityTotal += $lineItem->quantity;
            }
        }

        return $quantityTotal;
    }

    public function calculateSimpleDiscount()
    {
        return round($this->total - $this->calculateAdditionalTotal(false, true) - $this->calculateProductTotal(false, true) - $this->calculateShippingTotal(false, true), config('project.line_item_total_precision'));
    }

    public function getPaidAmount()
    {
        $paidAmount = 0;

        foreach($this->payments as $payment){
            if($payment->isSuccess){
                $paidAmount += CurrencyHelper::convert($payment->amount, $payment->currency, $this->currency, $this->conversion_rate);
            }
        }

        return $paidAmount;
    }

    public function getOutstandingAmount()
    {
        $paidAmount = $this->getPaidAmount();

        return abs($this->total - $paidAmount);
    }

    public function getProductLineItems()
    {
        $lineItems = [];

        foreach($this->lineItems as $lineItem){
            if($lineItem->isProduct){
                $lineItems[] = $lineItem;
            }
        }

        return $lineItems;
    }

    public function getProductQuantity($product_id)
    {
        $count = 0;

        foreach($this->getProductLineItems() as $lineItem){
            if($lineItem->line_item_id == $product_id){
                $count += $lineItem->quantity;
            }
        }

        return $count;
    }

    public function getShippingLineItems()
    {
        $lineItems = [];

        foreach($this->lineItems as $lineItem){
            if($lineItem->isShipping){
                $lineItems[] = $lineItem;
            }
        }

        return $lineItems;
    }

    public function getShippingLineItem($idx=0)
    {
        $shippingLineItems = $this->getShippingLineItems();

        return isset($shippingLineItems[$idx])?$shippingLineItems[$idx]:null;
    }

    public function getShippingMethod()
    {
        $shippingLineItem = $this->getShippingLineItem();

        return $shippingLineItem?$shippingLineItem->shippingMethod:null;
    }

    public function getSelectedShippingMethod()
    {
        $shippingLineItem = $this->getShippingLineItem();

        return $shippingLineItem?$shippingLineItem->getData('shipping_method'):null;
    }

    public function getCartPriceRuleLineItems()
    {
        $lineItems = [];

        foreach($this->lineItems as $lineItem){
            if($lineItem->isCartPriceRule){
                $lineItems[] = $lineItem;
            }
        }

        return $lineItems;
    }

    public function getCouponLineItems()
    {
        $lineItems = [];

        foreach($this->lineItems as $lineItem){
            if($lineItem->isCoupon){
                $lineItems[] = $lineItem;
            }
        }

        return $lineItems;
    }

    public function getTaxLineItems()
    {
        $taxLineItems = [];

        foreach($this->lineItems as $lineItem){
            if($lineItem->isTax){
                $taxLineItems[] = $lineItem;
            }
        }

        return $taxLineItems;
    }

    public function getTotalWeight()
    {
        $weight = 0;

        foreach($this->getProductLineItems() as $productLineItem){
            $weight += abs($productLineItem->product->getShippingInformation()['weight'] * $productLineItem->quantity);
        }

        return $weight?:1000;
    }

    public function eligibleForFreeShipping()
    {
        $eligible = false;

        foreach($this->lineItems as $lineItem){
            if($lineItem->isFreeShipping){
                $eligible = true;
                break;
            }
        }

        return $eligible;
    }

    //Scopes
    public function scopeJoinBillingProfile($query)
    {
        $profileDetailQuery = with(new Profile())->details();

        $query->leftJoin($profileDetailQuery->getRelated()->getTable().' AS BFNAME', function($join) use ($profileDetailQuery){
            $join->on('BFNAME.'.$profileDetailQuery->getPlainForeignKey(), '=', $this->getTable().'.'.$this->billingProfile()->getForeignKey())
                ->where('BFNAME.identifier', '=', 'first_name');
        });

        $query->leftJoin($profileDetailQuery->getRelated()->getTable().' AS BLNAME', function($join) use ($profileDetailQuery){
            $join->on('BLNAME.'.$profileDetailQuery->getPlainForeignKey(), '=', $this->getTable().'.'.$this->billingProfile()->getForeignKey())
                ->where('BLNAME.identifier', '=', 'last_name');
        });

        $query->addSelect(DB::raw($this->getTable().'.*, CONCAT_WS(" ", BFNAME.value, BLNAME.value) AS billing_full_name'));
    }

    public function scopeJoinShippingProfile($query)
    {
        $profileDetailQuery = with(new Profile())->details();

        $query->leftJoin($profileDetailQuery->getRelated()->getTable().' AS SFNAME', function($join) use ($profileDetailQuery){
            $join->on('SFNAME.'.$profileDetailQuery->getPlainForeignKey(), '=', $this->getTable().'.'.$this->shippingProfile()->getForeignKey())
                ->where('SFNAME.identifier', '=', 'first_name');
        });

        $query->leftJoin($profileDetailQuery->getRelated()->getTable().' AS SLNAME', function($join) use ($profileDetailQuery){
            $join->on('SLNAME.'.$profileDetailQuery->getPlainForeignKey(), '=', $this->getTable().'.'.$this->shippingProfile()->getForeignKey())
                ->where('SLNAME.identifier', '=', 'last_name');
        });

        $query->addSelect(DB::raw($this->getTable().'.*, CONCAT_WS(" ", SFNAME.value, SLNAME.value) AS shipping_full_name'));
    }

    public function scopeJoinOutstanding($query)
    {
        $paymentQueryQuery = Payment::selectRaw('order_id, SUM(amount) AS paid_amount')
            ->whereRaw('status = \''.Payment::STATUS_SUCCESS.'\'')
            ->groupBy('order_id');

        $query
            ->leftJoin(DB::raw('('.$paymentQueryQuery->toSql().') AS P'), 'P.order_id', '=', $this->getTable().'.id');

        $query->addSelect(DB::raw($this->getTable().'.*, P.*, (total - COALESCE(P.paid_amount, 0)) AS outstanding'));
    }

    public function scopeCheckout($query)
    {
        $query->whereNotIn('status', [self::STATUS_CART, self::STATUS_ADMIN_CART]);
    }

    public function scopeUsageCounted($query)
    {
        $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_PROCESSING, self::STATUS_SHIPPED, self::STATUS_COMPLETED]);
    }

    public function scopeProcessed($query)
    {
        $query->whereIn('status', config('project.processed_order_status', self::$processedStatus));
    }

    public function scopeWhereHasLineItem($query, $line_item_id, $line_item_type)
    {
        $query->whereHas('lineItems', function($qb) use ($line_item_id, $line_item_type){
            $qb->where('line_item_id', $line_item_id)->where('line_item_type', $line_item_type);
        });
    }

    public function scopeBelongsToStore($query, $stores)
    {
        $query->whereIn('store_id', $stores);
    }

    //Accessors
    public function getItemsCountAttribute()
    {
        $productLineItems = $this->getProductLineItems();

        $count = 0;
        foreach($productLineItems as $productLineItem){
            $count += $productLineItem->quantity;
        }

        return $count;
    }

    public function getIsCancellableAttribute()
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING]);
    }

    public function getIsCompleteableAttribute()
    {
        return Gate::allows('process_order', [$this, self::STATUS_COMPLETED]);
    }

    public function getIsShippableAttribute()
    {
        return Gate::allows('process_order', [$this, self::STATUS_SHIPPED]);
    }

    public function getIsPrintableAttribute()
    {
        return Gate::allows('process_order', [$this, 'print']);
    }

    public function getIsProcessableAttribute()
    {
        return Gate::allows('process_order', [$this, self::STATUS_PROCESSING]);
    }

    public function getIsEditableAttribute()
    {
        return in_array($this->status, [self::STATUS_ADMIN_CART, self::STATUS_CART, self::STATUS_PENDING]);
    }

    public function getIsDeleteableAttribute()
    {
        return in_array($this->status, [self::STATUS_ADMIN_CART, self::STATUS_CART]);
    }

    public function getIsCheckoutAttribute()
    {
        return !in_array($this->status, [self::STATUS_ADMIN_CART, self::STATUS_CART]);
    }

    public function getStatusLabelAttribute()
    {
        $label = self::getStatusOptions($this->status, TRUE);

        return $label;
    }

    public function getShippingInformationAttribute()
    {
        if($this->shippingProfile){
            $this->shippingProfile->fillDetails();
        }

        return $this->shippingProfile;
    }

    public function getBillingInformationAttribute()
    {
        if($this->billingProfile){
            $this->billingProfile->fillDetails();
        }

        return $this->billingProfile;
    }

    public function getAdditionalFieldsAttribute()
    {
        $additionalFields = $this->getData('additional_fields', []);

        return $additionalFields;
    }

    //Mutators
    public function setAdditionalFieldsAttribute($additionalFields)
    {
        $this->saveData(['additional_fields' => $additionalFields]);
    }

    //Static
    public static function getStatusOptions($option=null, $all=false)
    {
        $array = [
            self::STATUS_CART => 'Cart',
            self::STATUS_ADMIN_CART => 'Admin Cart',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PROCESSING => 'Processing',
            self::STATUS_SHIPPED => 'Shipped',
            self::STATUS_COMPLETED => 'Completed',
        ];

        if(!$all){
            unset($array[self::STATUS_CART]);
            unset($array[self::STATUS_ADMIN_CART]);
        }

        if(empty($option)){
            return $array;
        }

        return (isset($array[$option]))?$array[$option]:$array;
    }

    protected static function boot()
    {
        parent::boot();

        static::deleted(function($model){
            if($model->forceDeleting){
                if($model->billingProfile){
                    $model->billingProfile->delete();
                }

                if($model->shippingProfile){
                    $model->shippingProfile->delete();
                }
            }
        });
    }
}
