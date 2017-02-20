<?php

namespace Kommercio\Listeners;

use Kommercio\Events\RewardPointEvent;
use Kommercio\Models\Log;
use Kommercio\Models\RewardPoint\RewardPointTransaction;

class RewardPointListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Handle the event.
     *
     * @param  OrderUpdate  $event
     * @return void
     */
    public function handle(RewardPointEvent $event)
    {
        if($event->type == 'approve'){
            $this->approveRewardPointTransaction($event->rewardPointTransaction);
        }elseif($event->type == 'reject'){
            $this->rejectRewardPointTransaction($event->rewardPointTransaction);
        }
    }

    protected function approveRewardPointTransaction(RewardPointTransaction $rewardPointTransaction)
    {
        if($rewardPointTransaction->type == RewardPointTransaction::TYPE_ADD){
            $rewardPointTransaction->customer->increment('reward_points', $rewardPointTransaction->amount);
        }else{
            $rewardPointTransaction->customer->decrement('reward_points', $rewardPointTransaction->amount);
        }

        Log::log('reward_point.approve', 'Reward point transaction is approved.', $rewardPointTransaction, $rewardPointTransaction->amount);
    }

    protected function rejectRewardPointTransaction(RewardPointTransaction $rewardPointTransaction)
    {
        Log::log('reward_point.reject', 'Reward point transaction is rejected.', $rewardPointTransaction, $rewardPointTransaction->amount);
    }
}
