<?php

namespace Kommercio\Models\Interfaces;

interface CacheableInterface{
    /**
     * Get all Cache keys in the model
     * Don't forget to list all caches used in the model
     *
     * @return array
     */
    public function getCacheKeys();
}