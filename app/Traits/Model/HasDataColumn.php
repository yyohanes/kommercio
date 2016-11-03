<?php

namespace Kommercio\Traits\Model;

trait HasDataColumn
{
    public function saveData($data, $immediateSave = false)
    {
        $oldData = unserialize($this->data);
        $oldData = $oldData?$oldData:[];

        $data = array_merge($oldData, $data);

        $this->data = serialize($data);

        if($immediateSave){
            $this->save();
        }
    }

    public function hasData($key)
    {
        $data = unserialize($this->data);

        return !empty(array_get($data, $key));
    }

    public function setData($key, $value)
    {
        $data = unserialize($this->data);

        array_set($data, $key, $value);

        $this->data = serialize($data);

        return $data;
    }

    public function getData($attribute=null, $default=null)
    {
        $data = unserialize($this->data);
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
        $data = unserialize($this->data);
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