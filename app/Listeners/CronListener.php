<?php

namespace Kommercio\Listeners;

use Kommercio\Events\Cron as CronEvent;

class CronListener
{
    protected function onStartOfDayRun()
    {

    }

    protected function onMinuteRun()
    {

    }

    protected function onFifteenMinutesRun()
    {
        /* Toggle Date Models */
        $toggleDateModels = [
            '\Kommercio\Models\ProductDetail',
            '\Kommercio\Models\PriceRule',
            '\Kommercio\Models\PriceRule\CartPriceRule',
            '\Kommercio\Models\CMS\MenuItem',
            '\Kommercio\Models\CMS\Page',
            '\Kommercio\Models\CMS\Banner',
            '\Kommercio\Models\CMS\Block',
        ];

        foreach($toggleDateModels as $toggleDateModel){
            $models = $toggleDateModel::all();

            foreach($models as $model){
                $model->toggleByDate();
                $model->save();
            }
        }
        /* End Toggle Date Models */
    }

    /**
     * Handle the event.
     *
     * @param  CronEvent  $event
     * @return void
     */
    public function handle(CronEvent $event)
    {
        $type = $event->type;

        if($type == 'minute'){
            $this->onMinuteRun();
        }elseif($type == 'fifteen_minutes'){
            $this->onFifteenMinutesRun();
        }elseif($type == 'start_of_day'){
            $this->onStartOfDayRun();
        }
    }
}
