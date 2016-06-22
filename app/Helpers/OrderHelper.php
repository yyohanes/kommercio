<?php

namespace Kommercio\Helpers;

use Illuminate\Http\Request;
use Kommercio\Models\Customer;
use Kommercio\Models\Order\LineItem;
use Kommercio\Models\Order\Order;
use Kommercio\Models\Order\OrderComment;
use Kommercio\Models\Order\Payment;
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
        if($request->has('profile.email')){
            $customer_email = $request->input('profile.email');
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
            if($lineItemDatum['line_item_type'] == 'product' && (empty($lineItemDatum['quantity']) || empty($lineItemDatum['sku']))){
                continue;
            }

            $lineItem = new LineItem();
            $lineItem->processData($lineItemDatum, $count);
            $order->lineItems[] = $lineItem;

            $count += 1;
        }

        return $order;
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
}