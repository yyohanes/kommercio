<?php

namespace Kommercio\Models\Order;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class OrderLimitDayRule extends Model
{
    public $timestamps = FALSE;

    protected $casts = [
        'monday' => 'boolean',
        'tuesday' => 'boolean',
        'wednesday' => 'boolean',
        'thursday' => 'boolean',
        'friday' => 'boolean',
        'saturday' => 'boolean',
        'sunday' => 'boolean',
    ];
    protected $guarded = [];

    //Relations
    public function orderLimit()
    {
        return $this->belongsTo('Kommercio\Models\Order\OrderLimit');
    }

    //Methods
    public function check(Carbon $date)
    {
        $day = strtolower($date->format('l'));
        return $this->$day;
    }
}
