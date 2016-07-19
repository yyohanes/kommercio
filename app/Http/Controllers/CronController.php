<?php

namespace Kommercio\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Kommercio\Events\Cron as CronEvent;
use Kommercio\Facades\ProjectHelper;

class CronController extends Controller
{
    public function __construct(Request $request)
    {
        if($request->get('cron_token') != 'kommercio_proudly_built_by_yohanes'){
            abort(404);
        }
    }
}
