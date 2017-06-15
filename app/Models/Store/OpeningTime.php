<?php

namespace Kommercio\Models\Store;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Kommercio\Models\Store;

class OpeningTime extends Model
{
    /**
     * @var array
     */
    const DAYS = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

    protected $table = 'store_opening_times';
    protected $fillable = ['name', 'date_from', 'date_to', 'time_from', 'time_to', 'open', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday', 'store_id', 'sort_order'];
    protected $casts = [
        'open' => 'boolean',
        'monday' => 'boolean',
        'tuesday' => 'boolean',
        'wednesday' => 'boolean',
        'thursday' => 'boolean',
        'friday' => 'boolean',
        'saturday' => 'boolean',
        'sunday' => 'boolean'
    ];
    protected $appends = [
        'isEveryday'
    ];

    // Methods

    /**
     * Check if open on given time
     * @param Carbon $time
     * @return boolean
     */

    public function isOpen(Carbon $time)
    {
        // Check against Date From
        if(!empty($this->date_from)){
            $dateFrom = Carbon::createFromFormat('Y-m-d H:i:s', $this->date_from.' 00:00:00');

            if($dateFrom->lte($time)){

            }else{
                return !$this->open;
            }
        }

        // Check against Date To
        if(!empty($this->date_to)){
            $dateTo = Carbon::createFromFormat('Y-m-d H:i:s', $this->date_to.' 00:00:00');
            $dateTo->modify('+1 day');

            if($dateTo->gt($time)){

            }else{
                return !$this->open;
            }
        }

        // Check against Time From
        if(!empty($this->time_from)){
            $dateFrom = clone $time;
            $dateFrom->setTimeFromTimeString($this->time_from);

            if($dateFrom->lte($time)){

            }else{
                return !$this->open;
            }
        }

        // Check against Time To
        if(!empty($this->time_to)){
            $dateTo = clone $time;
            $dateTo->setTimeFromTimeString($this->time_to);

            if($dateTo->gt($time)){

            }else{
                return !$this->open;
            }
        }

        // Check against days if not everyday
        if (!$this->isEveryday) {
            $day = strtolower($time->format('l'));
            $dayOpen = $this->$day;

            if (!$dayOpen) {
                return !$this->open;
            }
        }

        return $this->open;
    }

    // Accessors

    /**
     * Everyday means all days are TRUE or all days NULL
     * @return bool
     */
    public function getIsEverydayAttribute()
    {
        $firstDay = self::DAYS[0];
        $prevDay = $this->$firstDay;

        foreach (self::DAYS as $idx => $day) {
            if ($prevDay !== $this->$day) {
                return FALSE;
            }

            $prevDay = $this->$day;
        }

        return TRUE;
    }

    // Scopes

    /**
     * Query where given date is within date_from and date_to
     *
     * @param $query
     * @param string $date Date string with format of Y-m-d
     */
    public function scopeWithinDate($query, $date = null)
    {
        if(!$date){
            $date = Carbon::now()->format('Y-m-d');
        }

        $query->where(function($query) use ($date){
            $query
                ->whereNull('date_from')
                ->orWhere('date_from', '<=', $date);
        });

        $query->where(function($query) use ($date){
            $query
                ->whereNull('date_to')
                ->orWhere('date_to', '>=', $date);
        });
    }

    /**
     * Query where given time is within time_from and time_to
     *
     * @param $query
     * @param string $time Time string with format of H:i:s
     */
    public function scopeWithinTime($query, $time = null)
    {
        if(!$time){
            $time = Carbon::now()->format('H:i:s');
        }

        $query->where(function($query) use ($time){
            $query
                ->whereNull('time_from')
                ->orWhere('time_from', '<=', $time);
        });

        $query->where(function($query) use ($time){
            $query
                ->whereNull('time_to')
                ->orWhere('time_to', '>=', $time);
        });
    }

    // Relations
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
