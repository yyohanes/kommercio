<?php

namespace Kommercio\Traits\Model;

use Illuminate\Support\Facades\Log;

trait HasDataColumn
{
    public function saveData($data, $immediateSave = false)
    {
        $oldData = $this->unserializeData();

        $data = array_merge($oldData, $data);

        $this->data = serialize($data);

        if($immediateSave){
            $this->save();
        }
    }

    public function hasData($key)
    {
        $data = $this->unserializeData();

        return !empty(array_get($data, $key));
    }

    public function setData($key, $value)
    {
        $data = $this->unserializeData();

        array_set($data, $key, $value);

        $this->data = serialize($data);

        return $data;
    }

    public function getData($attribute=null, $default=null)
    {
        $data = $this->unserializeData();
        if(is_bool($data)){
            $data = [];
        }

        if($attribute){
            return array_get($data, $attribute, $default);
        }

        return $data;
    }

    public function unsetData($attribute=null, $immediateSave = false)
    {
        $data = $this->unserializeData();
        if(is_bool($data)){
            $data = [];
        }

        if($attribute){
            array_pull($data, $attribute);
        }else{
            $data = null;
        }

        $this->data = serialize($data);

        if($immediateSave){
            $this->save();
        }
    }

    protected function unserializeData()
    {
        $data = [];

        try {
            $data = unserialize($this->data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        return $data?$data:[];
    }

    //Scopes
    public function scopeSearchData($query, $key, $value, $whereNot = false)
    {
        $regexpFunc = 'REGEXP';

        if($whereNot){
            $regexpFunc = 'NOT REGEXP';
        }

        if(is_array($value)){
            foreach($value as $idx=>$value_single){
                if($idx == 0){
                    $query->whereRaw('data '.$regexpFunc.' \'.*"'.$key.'";s:[0-9]+:"'.$value_single.'".*\'');
                }else{
                    $query->orWhereRaw('data '.$regexpFunc.' \'.*"'.$key.'";s:[0-9]+:"'.$value_single.'".*\'');
                }
            }
        }else{
            $query->whereRaw('data '.$regexpFunc.' \'.*"'.$key.'";s:[0-9]+:"'.$value.'".*\'');
        }
    }
}