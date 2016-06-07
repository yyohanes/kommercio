<?php

namespace Kommercio\Traits\Model;

use Carbon\Carbon;

trait ToggleDate
{
    protected $toggleDates = [];
    protected $toggleDateFormat = 'Y-m-d H:i';

    public function getDates()
    {
        foreach($this->toggleFields as $toggleField){
            $this->toggleDates[] = $toggleField.'_date_from';
            $this->toggleDates[] = $toggleField.'_date_to';
        }

        if(!empty($this->fillable)){
            $this->fillable = array_merge($this->fillable, $this->toggleDates);
        }

        $this->dates = array_merge($this->dates, $this->toggleDates);

        return parent::getDates();
    }

    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->toggleDates)) {
            if(empty($value)){
                $this->attributes[$key] = NULL;
            }else{
                $format = $this->toggleDateFormat;

                $this->attributes[$key] = Carbon::createFromFormat($format, $value);
            }
        } else {
            return parent::setAttribute($key, $value);
        }

        return $this;
    }

    public function togglePropertyByTime($field)
    {
        if(!empty($this->{$field.'_date_from'}) || !empty($this->{$field.'_date_to'})){
            $today = Carbon::now();
            $from = empty($this->{$field.'_date_from'})?clone $today:clone $this->{$field.'_date_from'};

            $to = empty($this->{$field.'_date_to'})?clone $today:clone $this->{$field.'_date_to'};
            $to->modify('+1 day');

            if($today->gte($from) && $today->lte($to)){
                $this->$field = true;
            }else{
                $this->$field = false;
            }
        }
    }

    public function toggleByDate()
    {
        foreach($this->toggleFields as $toggleField){
            $this->togglePropertyByTime($toggleField);
        }
    }

    protected static function boot() {
        parent::boot();

        static::saving(function($model) {
            $model->toggleByDate();
        });
    }
}