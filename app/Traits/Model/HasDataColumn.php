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
        if(is_array($value)){
            foreach($value as $idx=>$value_single){
                if($idx == 0){
                    $query->whereRaw('data REGEXP \'.*"'.$key.'";s:[0-9]+:"'.$value_single.'".*\'');
                }else{
                    $query->orWhereRaw('data REGEXP \'.*"'.$key.'";s:[0-9]+:"'.$value_single.'".*\'');
                }
            }
        }else{
            $query->whereRaw('data REGEXP \'.*"'.$key.'";s:[0-9]+:"'.$value_single.'".*\'');
        }
    }
}