<?php

namespace Kommercio\Models\Order;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Kommercio\Facades\CurrencyHelper;
use Kommercio\Facades\PriceFormatter;
use Kommercio\Facades\ProjectHelper;
use Kommercio\Models\Interfaces\AuthorSignatureInterface;
use Kommercio\Models\Order\DeliveryOrder\DeliveryOrder;
use Kommercio\Models\Order\DeliveryOrder\DeliveryOrderItem;
use Kommercio\Models\PriceRule\CartPriceRule;
use Kommercio\Models\PriceRule\Coupon;
use Kommercio\Models\Product;
use Kommercio\Models\Profile\Profile;
use Kommercio\Models\RewardPoint\RewardPointTransaction;
use Kommercio\Models\RewardPoint\RewardRule;
use Kommercio\Models\ShippingMethod\ShippingMethod;
use Kommercio\Models\Tag;
use Kommercio\Models\Tax;
use Kommercio\Traits\Model\AuthorSignature;
use Kommercio\Traits\Model\FlatIndexable;
use Kommercio\Traits\Model\HasDataColumn;
use Kommercio\Models\Order\Payment;

class Order extends Model implements AuthorSignatureInterface
{
    use SoftDeletes, AuthorSignature, HasDataColumn, FlatIndexable;

    const STATUS_CANCELLED = 'cancelled';
    const STATUS_ADMIN_CART = 'admin_cart';
    const STATUS_CART = 'cart';
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_COMPLETED = 'completed';

    public static $processedStatus = [self::STATUS_PENDING, self::STATUS_PROCESSING];

    protected $originalLineItems;
    protected $guarded = ['shippingProfile', 'billingProfile', 'invoices'];
    protected $dates = ['deleted_at', 'delivery_date', 'checkout_at'];

    protected $flatTable = 'orders_index';
    // TODO: Add more keys to flat index
    protected $flatIndexables = [
        'billingProfile.email',
        'billingProfile.phone_number',
        'billingProfile.address_1',
        'billingProfile.address_2',
        'billingProfile.postal_code',
        'shippingProfile.email',
        'shippingProfile.phone_number',
        'shippingProfile.address_1',
        'shippingProfile.address_2',
        'shippingProfile.postal_code',
        'getShippingLineItem().line_item_id',
        'getSelectedShippingMethod()',
        'ip_address',
        'status',
        'checkout_at',
        'delivery_date',
        'store_id',
    ];

    //Relations
    public function lineItems()
    {
        return $this->hasMany('Kommercio\Models\Order\LineItem')->where('temporary', false)->whereNull('parent_id')->orderBy('sort_order', 'ASC');
    }

    public function allLineItems()
    {
        return $this->hasMany('Kommercio\Models\Order\LineItem')->where('temporary', false)->orderBy('sort_order', 'ASC');
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
        return $this->hasMany('Kommercio\Models\Order\Payment')->counted();
    }

    public function deliveryOrders()
    {
        return $this->hasMany('Kommercio\Models\Order\DeliveryOrder\DeliveryOrder');
    }

    public function invoices()
    {
        return $this->hasMany('Kommercio\Models\Order\Invoice');
    }

    public function comments()
    {
        return $this->hasMany('Kommercio\Models\Order\OrderComment');
    }

    public function internalMemos()
    {
        return $this->comments()->internalMemo()->orderBy('created_at', 'DESC');
    }

    public function externalMemos()
    {
        return $this->comments()->externalMemo()->orderBy('created_at', 'DESC');
    }

    public function rewardPointTransactions()
    {
        return $this->hasMany('Kommercio\Models\RewardPoint\RewardPointTransaction')->orderBy('created_at', 'DESC');
    }

    public function tags() {
        return $this->morphToMany(Tag::class, 'taggable');
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
            $this->unsetData('saved_shipping_profile');
            $this->unsetData('saved_billing_profile');
            $this->calculateTotal();

            $time = $this->freshTimestamp();
            $this->setCreatedAt($time);
            $this->setUpdatedAt($time);

            $this->save();
        }
    }

    public function getProfileOrNew($type)
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

        return $profile;
    }

    public function clearCart()
    {
        $this->unsetData('checkout_step', true);

        foreach($this->lineItems as $lineItem){
            $lineItem->clearData();
            $lineItem->delete();
            if(!$lineItem->isShipping){

            }
        }
    }

    public function addToCart(Product $product, $quantity = 1, $options = [])
    {
        $existingLineItems = $this->getProductLineItems();

        //if already exists and not customized or composite
        $alreadyExist = FALSE;
        foreach($existingLineItems as $existingLineItem){
            if($existingLineItem->line_item_id == $product->id && empty($options['children']) && empty($options['configurations'])){
                $alreadyExist = TRUE;
                $existingLineItem->quantity += $quantity;
                $existingLineItem->calculateTotal();
                $existingLineItem->save();
                break;
            }
        }

        if(!$alreadyExist){
            $lineItemDatum = [
                'line_item_type' => 'product',
                'net_price' => $product->getNetPrice(),
                'quantity' => $quantity,
                'sku' => $product->sku,
                'configurations' => isset($options['configurations'][$product->id])?$options['configurations'][$product->id]:[]
            ];

            if(!empty($options['children'])){
                foreach($options['children'] as $compositeId => $children){
                    $lineItemDatum['children'][$compositeId] = [];

                    foreach($children as $child){
                        $childProduct = Product::findOrFail($child['product_id']);
                        $lineItemDatum['children'][$compositeId][] = [
                            'line_item_id' => $child['product_id'],
                            'line_item_type' => 'product',
                            'net_price' => $childProduct->getNetPrice(),
                            'quantity' => $child['quantity'],
                            'sku' => $childProduct->sku,
                            'product_composite_id' => $compositeId,
                            'configurations' => isset($options['configurations'][$childProduct->id])?$options['configurations'][$childProduct->id]:[]
                        ];
                    }
                }
            }

            if(!empty($options['line_item_id'])){
                $lineItem = $this->findLineItem($options['line_item_id']);
            }

            if(empty($lineItem)){
                $lineItem = new LineItem();
            }

            $lineItem->order()->associate($this);
            $lineItem->processData($lineItemDatum);
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

    public function addCoupon(Coupon $coupon)
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
                'line_item_type' => 'coupon',
                'coupon_id' => $coupon->id,
                'lineitem_total_amount' => 0, //This is purposely set to 0 because it's not possible to calculate now. Calculation will be done later at Controller level
            ]);
            $lineItem->save();

            $this->load('lineItems');
        }

        return $this;
    }

    public function removeCoupon(Coupon $coupon)
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
                foreach($shippingOptions as $shippingOptionId => $shippingOption){
                    if($shippingOptionId == $selected_method){
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
                'shipping_description' => isset($shippingOption['description'])?$shippingOption['description']:null,
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

    /**
     * Get Delivery Orders with Shipped status
     *
     * @param string $status Delivery order status
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDeliveryOrdersByStatus($status)
    {
        $status = is_array($status)?$status:[$status];

        return $this->deliveryOrders->filter(function($value, $key) use ($status) {
            return in_array($value->status, $status);
        });
    }

    public function generateReference($last_number = null)
    {
        $format = ProjectHelper::getConfig('order_options.reference_format');
        $formatElements = explode(':', $format);

        $counterLength = ProjectHelper::getConfig('order_options.reference_counter_length');

        $lastOrder = self::whereRaw("DATE_FORMAT(checkout_at, '%d-%m-%Y') = ?", [$this->checkout_at->format('d-m-Y')])
            ->where('store_id', $this->store_id)
            ->orderBy(DB::raw('CAST(order_number as UNSIGNED)'), 'DESC')
            ->first();

        if($last_number){
            $totalCheckedOutOrder = intval($last_number);
        }else{
            $totalCheckedOutOrder = $lastOrder?intval($lastOrder->order_number):0;
        }

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

        // Wrap in transaction so we can catch conflict Reference and re-generate
        DB::beginTransaction();

        $shouldCommit = true;

        try {
            $this->update([
                'reference' => $this->reference,
                'order_number' => $this->order_number
            ]);
        } catch(\Exception $e) {
            DB::rollback();

            $errorCode = $e->errorInfo[1];

            $shouldCommit = false;

            if ($errorCode == 1062) {
                $this->reference = $this->generateReference($this->order_number);
            }
        }

        if ($shouldCommit) {
            DB::commit();
        }

        return $this->reference;
    }

    public function generatePublicId()
    {
        $uuid = ProjectHelper::generateUuid();

        $this->public_id = $uuid;

        while(self::where('public_id', $uuid)->count() > 0){
            $uuid = $this->generatePublicId();
        }

        return $uuid;
    }

    public function processStocks()
    {
        $this->_processStocks($this->lineItems);
    }

    public function returnStocks()
    {
        foreach($this->lineItems as $lineItem){
            if($lineItem->isProduct){
                $lineItem->product->increaseStock($lineItem->quantity);
            }
        }
    }

    protected function _processStocks($lineItems)
    {
        foreach($lineItems as $lineItem){
            if($lineItem->isProduct){
                $lineItem->product->reduceStock($lineItem->quantity);
            }

            if($lineItem->children->count() > 0){
                $this->_processStocks($lineItem->children);
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

    public function calculateRewardRules()
    {
        if (!ProjectHelper::isFeatureEnabled('customer.reward_points')) {
            return 0;
        }

        $data = [
            'currency' => $this->currency,
            'store_id' => $this->store?$this->store->id:null,
        ];

        $rewardRules = RewardRule::getRewardRules($data);

        $rewardPoints = 0;

        foreach($rewardRules as $rewardRule){
            $rewardPoints += $rewardRule->calculateOrderRewardPoint($this);
        }

        return $rewardPoints;
    }

    public function deleteReviewRewardPointTransactions()
    {
        $existingReviewRewardPoints = $this->rewardPointTransactions()->where('status', RewardPointTransaction::STATUS_REVIEW)->get();
        foreach($existingReviewRewardPoints as $existingReviewRewardPoint){
            $existingReviewRewardPoint->delete();
        }
    }

    public function addRewardPoint($data = [])
    {
        $data += [
            'reason' => isset($data['reason'])?$data['reason']:'Reward Point for Order #'.$this->reference,
            'notes' => isset($data['notes'])?$data['notes']:null,
        ];

        $rewardPoints = $this->calculateRewardRules();

        if($rewardPoints > 0){
            $rewardPointTransaction = $this->customer->addRewardPoint($rewardPoints, $data, $this);

            return $rewardPointTransaction;
        }

        return false;
    }

    public function saveProfile($type, $data)
    {
        $profile = $this->getProfileOrNew($type);

        $profileRelation = $type.'Profile';

        // Standardized saved profile
        $fields = [
            'email',
            'first_name',
            'last_name',
            'full_name',
            'phone_number',
            'country_id',
            'state_id',
            'city_id',
            'district_id',
            'area_id',
            'postal_code',
            'address_1',
            'address_2'
        ];

        foreach ($fields as $field) {
            if (!isset($data[$field])) {
                $data[$field] = '';
            }
        }

        $profile->saveDetails($data);

        $this->load($profileRelation);
    }

    public function findLineItem($line_item_id)
    {
        return $this->lineItems->filter(function($value, $key) use ($line_item_id){
            return $value->id == $line_item_id;
        })->first();
    }

    public function calculateSubtotal()
    {
        $this->subtotal = $this->calculateProductTotal() + $this->calculateAdditionalTotal();
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

    public function getTotalBeforeExtras()
    {
        return $this->subtotal + $this->additional_total + $this->discount_total;
    }

    public function calculateTotal()
    {
        $subtotal = $this->calculateSubtotal();
        $shippingTotal = $this->calculateShippingTotal();
        $discountTotal = $this->calculateDiscountTotal();
        $taxTotal = $this->calculateTaxTotal();
        $taxError = $this->calculateTaxError();

        $this->total = PriceFormatter::round($subtotal + $shippingTotal + $discountTotal + $taxTotal);
        $beforeRoundingTotal = $this->total;

        $this->total = PriceFormatter::round($this->total, config('project.total_precision'), config('project.total_rounding'));

        $this->rounding_total = PriceFormatter::calculateRounding($beforeRoundingTotal, $this->total);

        return $this->total;
    }

    public function calculateTaxError()
    {
        $this->tax_error_total = 0;

        foreach($this->getTaxLineItems() as $taxLineItem){
            $this->tax_error_total += $taxLineItem->net_price - $taxLineItem->base_price;
        }

        if(!empty($this->tax_error_total)){
            $this->tax_error_total = PriceFormatter::round($this->tax_error_total);
        }else{
            $this->tax_error_total = 0;
        }

        return $this->tax_error_total;
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
        return round($this->calculateTotal() - $this->calculateAdditionalTotal(false, true) - $this->calculateProductTotal(false, true) - $this->calculateShippingTotal(false, true) - $this->rounding_total, config('project.line_item_total_precision'));
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

        return $this->calculateTotal() - $paidAmount;
    }

    /**
     * Get Product Line Items only
     *
     * @param bool $include_children Whether to include Line item children
     * @return array
     */
    public function getProductLineItems($include_children = false)
    {
        $lineItems = [];

        $queriedLineItems = $include_children?$this->allLineItems:$this->lineItems;

        foreach($queriedLineItems as $lineItem){
            if($lineItem->isProduct){
                $lineItems[] = $lineItem;
            }
        }

        return $lineItems;
    }

    public function getFeeLineItems()
    {
        $lineItems = [];

        foreach($this->lineItems as $lineItem){
            if($lineItem->isFee){
                $lineItems[] = $lineItem;
            }
        }

        return $lineItems;
    }

    public function getProductQuantity($product_id, $include_children = false)
    {
        $count = 0;

        foreach($this->getProductLineItems($include_children) as $lineItem){
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

    public function createDeliveryOrder($deliveredLineItems, $options = [])
    {
        $deliveryOrder = new DeliveryOrder($options);
        $deliveryOrder->status = DeliveryOrder::STATUS_PENDING;
        $deliveryOrder->store()->associate($this->store);
        $deliveryOrder->customer()->associate($this->customer);
        $deliveryOrder->shippingMethod()->associate($this->getShippingMethod());
        $deliveryOrder->order()->associate($this);
        $deliveryOrder->generateReference();

        if(!empty($options['data'])){
            $deliveryOrder->saveData($options['data']);
        }

        $deliveryOrder->save();

        if(!empty($options['shippingProfile'])){
            $shippingProfile = $deliveryOrder->getProfileOrNew();
            $shippingProfile->saveDetails($options['shippingProfile']);
        }

        $count = 0;
        foreach($this->getProductLineItems() as $idx => $productLineItem){
            if(!isset($deliveredLineItems[$productLineItem->id]) || empty($deliveredLineItems[$productLineItem->id]['quantity'])){
                continue;
            }

            $count += 1;

            $deliveryLineItem = new DeliveryOrderItem([
                'name' => $productLineItem->name,
                'quantity' => $deliveredLineItems[$productLineItem->id]['quantity'],
                'price' => $productLineItem->calculateNet(),
                'weight' => $productLineItem->product->weight?:0,
                'sort_order' => $count
            ]);

            $deliveryLineItem->product()->associate($productLineItem->product->id);
            $deliveryLineItem->lineItem()->associate($productLineItem);
            $deliveryLineItem->deliveryOrder()->associate($deliveryOrder->id);
            $deliveryLineItem->save();

            $deliveryOrder->load('items');
        }

        $deliveryOrder->calculateTotalWeight();
        $deliveryOrder->calculateTotalQuantity();
        $deliveryOrder->save();

        return $deliveryOrder;
    }

    //Scopes
    public function scopeJoinBillingProfile($query)
    {
        $profileDetailQuery = with(new Profile())->details();

        $query->leftJoin($profileDetailQuery->getRelated()->getTable().' AS BFNAME', function($join) use ($profileDetailQuery){
            $join->on('BFNAME.'.$profileDetailQuery->getForeignKeyName(), '=', $this->getTable().'.'.$this->billingProfile()->getForeignKey())
                ->where('BFNAME.identifier', '=', 'first_name');
        });

        $query->leftJoin($profileDetailQuery->getRelated()->getTable().' AS BLNAME', function($join) use ($profileDetailQuery){
            $join->on('BLNAME.'.$profileDetailQuery->getForeignKeyName(), '=', $this->getTable().'.'.$this->billingProfile()->getForeignKey())
                ->where('BLNAME.identifier', '=', 'last_name');
        });

        $query->addSelect(DB::raw($this->getTable().'.*, CONCAT_WS(" ", BFNAME.value, BLNAME.value) AS billing_full_name'));
    }

    public function scopeJoinShippingProfile($query)
    {
        $profileDetailQuery = with(new Profile())->details();

        $query->leftJoin($profileDetailQuery->getRelated()->getTable().' AS SFNAME', function($join) use ($profileDetailQuery){
            $join->on('SFNAME.'.$profileDetailQuery->getForeignKeyName(), '=', $this->getTable().'.'.$this->shippingProfile()->getForeignKey())
                ->where('SFNAME.identifier', '=', 'first_name');
        });

        $query->leftJoin($profileDetailQuery->getRelated()->getTable().' AS SLNAME', function($join) use ($profileDetailQuery){
            $join->on('SLNAME.'.$profileDetailQuery->getForeignKeyName(), '=', $this->getTable().'.'.$this->shippingProfile()->getForeignKey())
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
        $query->whereIn('status', self::getUsageCountedStatus());
    }

    public function scopeProcessed($query)
    {
        $query->whereIn('status', ProjectHelper::getConfig('order_options.processed_order_status', self::$processedStatus));
    }

    public function scopeWhereHasLineItem($query, $line_item_id, $line_item_type)
    {
        $query->whereHas('lineItems', function($qb) use ($line_item_id, $line_item_type){
            if(is_array($line_item_id)){
                $qb->whereIn('line_item_id', $line_item_id);
            }else{
                $qb->where('line_item_id', $line_item_id);
            }
            $qb->where('line_item_type', $line_item_type);
        });
    }

    public function scopeBelongsToStore($query, $stores)
    {
        $query->whereIn('store_id', $stores);
    }

    //Accessors

    /**
     * Get original line items
     * @return \Illuminate\Database\Eloquent\Collection $lineItems
     */
    public function getOriginalLineItemsAttribute()
    {
        $this->originalLineItems = $this->originalLineItems?:new \Illuminate\Database\Eloquent\Collection([]);

        return $this->originalLineItems;
    }

    public function getItemsCountAttribute()
    {
        $productLineItems = $this->getProductLineItems();

        $count = 0;
        foreach($productLineItems as $productLineItem){
            $count += $productLineItem->quantity;
        }

        return $count;
    }

    public function getProductsCountAttribute()
    {
        $productLineItems = $this->getProductLineItems();

        return count($productLineItems);
    }

    public function getIsCancellableAttribute()
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_PROCESSING]) || (Gate::allows('access', ['cancel_settled_order']) && !in_array($this->status, [self::STATUS_CANCELLED, Order::STATUS_COMPLETED]));
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
        return in_array($this->status, [self::STATUS_ADMIN_CART, self::STATUS_CART])
            || (Gate::allows('access', ['edit_settled_order'])
                && !in_array($this->status, [self::STATUS_CANCELLED, Order::STATUS_COMPLETED])
                && $this->getDeliveryOrdersByStatus([DeliveryOrder::getCountedStatus()])->count() == 0);
    }

    public function getIsDeleteableAttribute()
    {
        return in_array($this->status, [self::STATUS_ADMIN_CART, self::STATUS_CART]);
    }

    public function getIsCheckoutAttribute()
    {
        return !in_array($this->status, [self::STATUS_ADMIN_CART, self::STATUS_CART]);
    }

    /**
     * If order is finally completed or cancelled
     *
     * @return bool
     */
    public function getIsFinalAttribute()
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED]);
    }

    public function getIsFullyShippedAttribute()
    {
        $fully = false;

        foreach($this->getProductLineItems() as $productLineItem){
            $fully = $productLineItem->isFullyShipped;

            if(!$fully){
                break;
            }
        }

        return $fully;
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
        }else{
            return new Profile();
        }

        return $this->shippingProfile;
    }

    public function getBillingInformationAttribute()
    {
        if($this->billingProfile){
            $this->billingProfile->fillDetails();
        }else{
            return new Profile();
        }

        return $this->billingProfile;
    }

    public function getAdditionalFieldsAttribute()
    {
        $additionalFields = $this->getData('additional_fields', []);

        return $additionalFields;
    }

    public function getPaymentStatusAttribute()
    {
        $outstanding = $this->getOutstandingAmount();

        $status = 'unpaid';

        if($outstanding <= 0){
            $status = 'paid';
        }elseif($outstanding < $this->total){
            $status = 'partial';
        }

        return $status;
    }

    //Mutators

    /**
     * Set original line items
     * @param \Illuminate\Database\Eloquent\Collection $lineItems
     */
    public function setOriginalLineItemsAttribute($lineItems)
    {
        $this->originalLineItems = new \Illuminate\Database\Eloquent\Collection([]);
        foreach($lineItems as $lineItem){
            $this->originalLineItems->push($lineItem->replicate());
        }
    }

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

    /**
     * Return status of Order that are considered as counted (In Cart shouldn't be considered as counted)
     * @return array
     */
    public static function getUsageCountedStatus()
    {
        return [Order::STATUS_PENDING, Order::STATUS_PROCESSING, Order::STATUS_SHIPPED, Order::STATUS_COMPLETED];
    }

    public static function processAndStatusMap($process)
    {
        $array = [
            'confirmation' => self::STATUS_PENDING,
            'processing' => self::STATUS_PROCESSING,
            'shipped' => self::STATUS_SHIPPED,
            'completed' => self::STATUS_COMPLETED,
            'cancelled' => self::STATUS_CANCELLED,
        ];

        return $array[$process];
    }

    /**
     * Get orders based on date
     * @param Carbon $date Date of order
     * @parem string $type delivery_date / checkout_at
     * @return Collection Orders based on Date and Type
     */
    public static function getOrdersByDate($date, $type = 'checkout_at')
    {
        $qb = self::usageCounted()->orderBy('checkout_at', 'ASC');

        if($type == 'delivery_date'){
            $qb->where('delivery_date', $date);
        }else{
            $qb->where('checkout_at', $date);
        }

        return $qb->get();
    }

    /**
     * @param $public_id Public ID of the invoice
     * @return self
     */
    public static function findPublic($public_id)
    {
        return self::where('public_id', $public_id)->first();
    }
}
