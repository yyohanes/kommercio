<?php

namespace Kommercio\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'Kommercio\Events\OrderUpdate' => [
            'Kommercio\Listeners\OrderUpdateListener',
        ],
    ];

    public function __construct($app)
    {
        parent::__construct($app);

        //Add package Cron Listener
        if(file_exists(app_path('packages/project/src/Project/Listeners/CronListener.php'))){
            $listen['Kommercio\Events\Cron'] = ['Project\Listeners\CronListener'];
        }
    }

    /**
     * Register any other events for your application.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function boot(DispatcherContract $events)
    {
        parent::boot($events);

        //
    }
}
