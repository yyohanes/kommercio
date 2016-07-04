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

    public function getData($attribute=null)
    {
        $data = unserialize($this->data);

        if($attribute){
            return isset($data[$attribute])?$data[$attribute]:null;
        }

        return $data;
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