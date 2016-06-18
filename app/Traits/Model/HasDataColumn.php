<?php

namespace Kommercio\Traits\Model;

trait HasDataColumn
{
    public function saveData($data)
    {
        $oldData = unserialize($this->data);
        $oldData = $oldData?$oldData:[];

        $data = array_merge($oldData, $data);

        $this->data = serialize($data);
    }

    public function getData($attribute=null)
    {
        $data = unserialize($this->data);

        if($attribute){
            return isset($data[$attribute])?$data[$attribute]:null;
        }

        return $data;
    }

    //Scopes
    public function scopeSearchData($query, $key, $value)
    {
        $query->whereRaw('data REGEXP \'.*"'.$key.'";s:[0-9]+:"'.$value.'".*\'');
    }

    public function scopeOrSearchData($query, $key, $value)
    {
        $query->orWhereRaw('data REGEXP \'.*"'.$key.'";s:[0-9]+:"'.$value.'".*\'');
    }
}