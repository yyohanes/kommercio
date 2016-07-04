<?php

namespace Kommercio\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Kommercio\Facades\ProjectHelper;

class CronController extends Controller
{
    public function __construct(Request $request)
    {
        if($request->get('cron_token') != 'kommercio_proudly_built_by_yohanes'){
            abort(404);
        }
    }

    public function minute()
    {
        if(ProjectHelper::getConfig('cron_minute_is_running') == 'true'){
            abort(403, 'Cron minute is already running.');
        }

        //Flag global cron minute
        ProjectHelper::saveConfig('cron_minute_is_running', 'true');

        /* Toggle Date Models */
        $toggleDateModels = [
            '\Kommercio\Models\ProductDetail',
            '\Kommercio\Models\PriceRule',
            '\Kommercio\Models\PriceRule\CartPriceRule',
            '\Kommercio\Models\CMS\MenuItem',
            '\Kommercio\Models\CMS\Page',
        ];

        foreach($toggleDateModels as $toggleDateModel){
            $models = $toggleDateModel::all();

            foreach($models as $model){
                $model->toggleByDate();
                $model->save();
            }
        }
        /* End Toggle Date Models */

        //Unflag global cron minute
        ProjectHelper::saveConfig('cron_minute_is_running', 'false');
    }
}
