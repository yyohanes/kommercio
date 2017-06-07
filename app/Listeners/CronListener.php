<?php

namespace Kommercio\Listeners;

use Illuminate\Support\Facades\File;
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
        $dir = app_path('Models');
        $files = File::allFiles($dir);

        $toggleDateModels = array();

        foreach ($files as $file)
        {
            $className = '\Kommercio\Models\\'.str_replace('.php', '', str_replace('/', '\\', $file->getRelativePathname()));
            $traits = class_uses($className);

            if(in_array('Kommercio\Traits\Model\ToggleDate', $traits)){
                $toggleDateModels[] = $className;
            }
        }

        foreach($toggleDateModels as $toggleDateModel){
            $models = $toggleDateModel::all();

            foreach($models as $model){
                $model->isDateToggling = TRUE;
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
