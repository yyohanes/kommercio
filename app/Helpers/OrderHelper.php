<?php

namespace Kommercio\Helpers;

use Illuminate\Http\Request;
use Kommercio\Facades\PriceFormatter as PriceFormatterFacade;
use Kommercio\Facades\ProjectHelper as ProjectHelperFacade;
use Kommercio\Models\Customer;
use Kommercio\Models\Order\LineItem;
use Kommercio\Models\Order\Order;
use Kommercio\Models\Order\OrderComment;
use Kommercio\Models\Order\Payment;
use Kommercio\Models\PriceRule\CartPriceRule;
use Kommercio\Models\Product;
use Kommercio\Models\Tax;
use Kommercio\Models\User;

class OrderHelper
{
    public function getOrderStatusLabelClass($status)
    {
        $array = [
            Order::STATUS_ADMIN_CART => 'grey-mint',
            Order::STATUS_CART => 'grey-mint',
            Order::STATUS_CANCELLED => 'grey-steel',
            Order::STATUS_PENDING => 'yellow-lemon',
            Order::STATUS_PROCESSING => 'blue',
            Order::STATUS_SHIPPED => 'green',
            Order::STATUS_COMPLETED => 'green-jungle',
        ];

        return isset($array[$status])?$array[$status]:'default';
    }

    public function getPaymentStatusLabelClass($status)
    {
        $array = [
            Payment::STATUS_VOID => 'grey-steel',
            Payment::STATUS_FAILED => 'grey-steel',
            Payment::STATUS_PENDING => 'grey-mint',
            Payment::STATUS_REVIEW => 'yellow-lemon',
            Payment::STATUS_SUCCESS=> 'green-jungle',
        ];

        return isset($array[$status])?$array[$status]:'default';
    }

    public function createDummyOrderFromRequest(Request $request)
    {
        //Create dummy order for subTotal calculation
        $order = new Order();

        $customer_email = null;
        if($request->has('profile.email') || $request->has('billingProfile.email')){
            $customer_email = $request->input('profile.email', $request->has('billingProfile.email'));
            $customer = Customer::getByEmail($customer_email);

            if($customer){
                $order->customer()->associate($customer);
            }
        }

        $order->delivery_date = $request->input('delivery_date', null);
        $order->store_id = $request->input('store_id');
        $order->payment_method_id = $request->input('payment_method', null);
        $order->currency = $request->input('currency');
        $order->conversion_rate = 1;

        $count = 0;
        foreach($request->input('line_items', []) as $lineItemDatum){
            if($lineItemDatum['line_item_type'] == 'product' && (empty($lineItemDatum['quantity']) || (empty($lineItemDatum['sku']) || empty($lineItemDatum['line_item_id'])))){
                continue;
            }

            $lineItem = new LineItem();
            $lineItem->processData($lineItemDatum, $count);
            $order->lineItems[] = $lineItem;

            $count += 1;
        }

        return $order;
    }

    public function processLineItems(Request $request, $order, $freeEdit = true)
    {
        $cartPriceRules = $this->getCartRules($request, $order, $freeEdit);

        $taxes = ProjectHelperFacade::getActiveStore()->getTaxes();

        $count = 0;

        $lineItems = [];

        if($freeEdit){
            $existingLineItems = $order->lineItems->all();

            foreach($request->input('line_items', []) as $lineItemDatum) {
                if ($lineItemDatum['line_item_type'] == 'product' && empty($lineItemDatum['quantity'])) {
                    continue;
                }

                $lineItem = $this->reuseOrCreateLineItem($order, $existingLineItems, $count);

                $lineItem->processData($lineItemDatum, $count);

                $lineItems[] = $lineItem;
            }
        }else{
            $lineItems = $order->lineItems;

            $existingLineItems = $order->lineItems->all();

            foreach($lineItems as $idx => $lineItem){
                if($lineItem->isProduct || $lineItem->isFee || $lineItem->isShipping){
                    unset($existingLineItems[$idx]);
                }
            }
        }

        foreach($lineItems as $idx => $lineItem){
            if(!$lineItem->discountApplicable){
                continue;
            }

            if($lineItem->isProduct && empty($lineItem->quantity)){
                $lineItem->delete();
                continue;
            }

            $lineItemAmount = $lineItem->net_price;

            $priceRuleValue = 0;

            foreach($cartPriceRules as $cartPriceRule){
                if($cartPriceRule->offer_type == CartPriceRule::OFFER_TYPE_PRODUCT_DISCOUNT){
                    $productCartPriceRuleProducts = $cartPriceRule->getProducts();

                    if(!empty($productCartPriceRuleProducts) && !isset($productCartPriceRuleProducts[$lineItem->line_item_id])){
                        continue;
                    }
                }elseif($cartPriceRule->modification_type == CartPriceRule::MODIFICATION_TYPE_PERCENT){

                }elseif($cartPriceRule->modification_type == CartPriceRule::MODIFICATION_TYPE_AMOUNT && count($cartPriceRule->appliedLineItems) < 1){

                }else{
                    continue;
                }

                $priceRuleValue += $cartPriceRule->getNetValue($lineItemAmount);

                $cartPriceRule->total += $priceRuleValue * $lineItem->quantity;
                $lineItemAmount += $priceRuleValue;
            }
            $lineItem->discount_total = $priceRuleValue;

            if($lineItem->taxable){
                foreach($taxes as $tax){
                    $taxValue = [
                        'net' => 0,
                        'gross' => 0,
                        'rate_total' => 0
                    ];

                    $taxValue['gross'] = PriceFormatterFacade::round($tax->calculateTax($lineItemAmount));
                    $taxValue['net'] = PriceFormatterFacade::round($taxValue['gross']);
                    $taxValue['rate_total'] += $tax->rate;

                    $tax->total += $taxValue['net'] * $lineItem->quantity;
                }

                if(isset($taxValue)){
                    $lineItem->tax_total = $taxValue['net'];
                    $lineItem->tax_rate = $taxValue['rate_total'];
                }
            }

            $lineItem->calculateTotal();
            $lineItem->save();

            $count += 1;
        }

        foreach($cartPriceRules as $cartPriceRule){
            $priceRuleLineItemDatum = [
                'cart_price_rule_id' => $cartPriceRule->id,
                'line_item_type' => 'cart_price_rule',
                'lineitem_total_amount' => $cartPriceRule->total,
            ];

            $lineItem = $this->reuseOrCreateLineItem($order, $existingLineItems, $count);

            $lineItem->processData($priceRuleLineItemDatum, $count);
            $lineItem->save();
            $lineItems[] = $lineItem;

            $count += 1;
        }

        foreach($taxes as $tax){
            $taxLineItemDatum = [
                'tax_id' => $tax->id,
                'line_item_type' => 'tax',
                'lineitem_total_amount' => $tax->total,
            ];

            $lineItem = $this->reuseOrCreateLineItem($order, $existingLineItems, $count);

            $lineItem->processData($taxLineItemDatum, $count);
            $lineItem->save();
            $lineItems[] = $lineItem;

            $count += 1;
        }

        //Delete unused line items
        foreach($existingLineItems as $existingLineItem){
            $existingLineItem->delete();
        }
    }

    public function reuseOrCreateLineItem($order, &$existingLineItems, $count)
    {
        if(!isset($existingLineItems[$count])){
            $lineItem = new LineItem();
            $lineItem->order()->associate($order);
        }else{
            //Clone and reset existing line item and will eventually be updated with new data
            $lineItem = $existingLineItems[$count];
            unset($existingLineItems[$count]);

            $lineItem->clearData();
        }

        return $lineItem;
    }

    public function getCartRules(Request $request, $referencedOrder = null, $freeEdit = true)
    {
        $order = ($freeEdit || !$referencedOrder)?$this->createDummyOrderFromRequest($request):$referencedOrder;

        $subtotal = $order->calculateProductTotal() + $order->calculateAdditionalTotal();

        $shippings = [];
        foreach($order->getShippingLineItems() as $shippingLineItem){
            $shippings[] = $shippingLineItem->line_item_id;
        }

        $addedCoupons = [];

        if($referencedOrder){
            foreach($referencedOrder->getCouponLineItems() as $couponLineItem){
                $addedCoupons[] = $couponLineItem->line_item_id;
            }
        }

        $addedCoupons = array_unique(array_merge($addedCoupons, $request->input('added_coupons', [])));

        $options = [
            'subtotal' => $subtotal,
            'currency' => $order->currency,
            'store_id' => $order->store_id,
            'customer_email' => $order->customer?$order->customer->getProfile()->email:null,
            'shippings' => $shippings,
            'added_coupons' => $addedCoupons,
        ];

        $priceRules = CartPriceRule::getCartPriceRules($options);

        foreach($priceRules as $idx=>$priceRule){
            if(!$priceRule->validateUsage($options['customer_email'])['valid']){
                unset($priceRules[$idx]);
            }
        }

        return $priceRules;
    }

    public function saveOrderComment($message, $key, Order $order, User $author = NULL, $type = OrderComment::TYPE_INTERNAL)
    {
        $comment = new OrderComment([
            'body' => $message,
            'type' => $type
        ]);
        $comment->saveData([
            'key' => $key
        ]);

        $comment->order()->associate($order);

        if($author){
            $comment->saveData([
                'author_name' => $author->fullName
            ]);
        }

        return $comment->save();
    }

    public function convertFrontendCartRequest(Request $request)
    {
        $attributes = $request->all();

        $productLineItems = [];
        foreach($request->input('products', []) as $idx => $productLineItem){
            $product = Product::findOrFail($productLineItem['id']);
            $productLineItems[$idx] = [
                'line_item_type' => 'product',
                'line_item_id' => $productLineItem['id'],
                'net_price' => $product->getNetPrice(),
                'quantity' => $productLineItem['quantity']
            ];
        }
        $attributes['line_items'] = $productLineItems;

        $request->replace($attributes);
    }
}