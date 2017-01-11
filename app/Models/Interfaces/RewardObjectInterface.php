<?php

namespace Kommercio\Models\Interfaces;

interface RewardObjectInterface{
    /**
     * Get reward usage count
     * @return int
     */
    public function getRewardUsageCount();

    /**
     * Log reward usage
     * @return void
     */
    public function markRewardUsed();

    /**
     * Get Logs of reward usage
     * @return MorphMany
     */
    public function rewardUsageLogs();
}