<?php

namespace Kommercio\Traits\Model;

use Kommercio\Models\Order\OrderLimit;

trait OrderLimitTrait{
    protected function extractOrderLimit($orderLimits)
    {
        $sorted = [
            'has_date' => [
                OrderLimit::TYPE_PRODUCT => [],
                OrderLimit::TYPE_PRODUCT_CATEGORY => []
            ],
            'no_date' => [
                OrderLimit::TYPE_PRODUCT => [],
                OrderLimit::TYPE_PRODUCT_CATEGORY => []
            ]
        ];

        //Has date
        foreach($orderLimits as $orderLimit){
            if($orderLimit->hasDate()){
                $sorted['has_date'][$orderLimit->type][] = $orderLimit;
            }
        }

        //No date
        foreach($orderLimits as $orderLimit){
            if(!$orderLimit->hasDate()){
                $sorted['no_date'][$orderLimit->type][] = $orderLimit;
            }
        }

        foreach($sorted['has_date'] as $sortedWalk){
            if(!empty($sortedWalk)){
                return $sortedWalk[0];
            }
        }

        foreach($sorted['no_date'] as $sortedWalk){
            if(!empty($sortedWalk)){
                return $sortedWalk[0];
            }
        }
    }
}