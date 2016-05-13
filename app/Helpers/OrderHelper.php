<?php

namespace Kommercio\Helpers;

use Kommercio\Models\Order\Order;

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
}