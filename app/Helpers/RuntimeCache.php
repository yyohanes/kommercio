<?php

namespace Kommercio\Helpers;

use Illuminate\Support\Arr;

class RuntimeCache
{
    private static $_storage;

    public function has($key)
    {
        return Arr::has(static::$_storage, $key);
    }

    public function set($key, $value)
    {
        return Arr::set(static::$_storage, $key, $value);
    }

    public function get($key, $default = null)
    {
        return Arr::get(static::$_storage, $key, $default);
    }

    public function forget($keys)
    {
        return Arr::forget(static::$_storage, $keys);
    }

    public function getOrSet($key, $closure)
    {
        if(!$this->has($key)){
            $this->set($key, $closure());
        }

        $value = $this->get($key);

        return $value;
    }
}