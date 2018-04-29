<?php

namespace Kommercio\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Event;
use Kommercio\Events\Cron as CronEvent;

class Kernel extends ConsoleKernel
{
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
    }

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //Clear cache every midnight
        $schedule->command('cache:clear')->daily()->withoutOverlapping();

        //Run minute task
        $schedule->call(function(){
            Event::fire(new CronEvent('minute'));
        })->name('every-minute-task')->cron('* * * * * *')->withoutOverlapping();

        //Run 15 mins task
        $schedule->call(function(){
            Event::fire(new CronEvent('fifteen_minutes'));
        })->name('fifteen-minutes-task')->cron('*/15 * * * * *')->withoutOverlapping();

        //Run every midnight
        $schedule->call(function(){
            Event::fire(new CronEvent('midnight'));
        })->name('midnight-task')->daily()->withoutOverlapping();

        //Run daily start of day
        $schedule->call(function(){
            Event::fire(new CronEvent('start_of_day'));
        })->name('start-of-day-task')->dailyAt('08:00')->withoutOverlapping();

        //Run queue every minute if QUEUE_WORKER is scheduler
        if (env('QUEUE_WORKER') == 'scheduler') {
            $schedule->command('queue:work --tries=10')->everyMinute()->withoutOverlapping();
        }
    }
}
