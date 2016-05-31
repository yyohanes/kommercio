<?php

namespace Kommercio\Helpers;

use Kommercio\Models\Order\Order;
use Kommercio\Models\Order\Payment;

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
}