<?php

namespace Kommercio\Http\Controllers\Services;

use Kommercio\Http\Controllers\Controller;
use Kommercio\Jobs\DHLJob;
use Kommercio\Models\Order\Order;

class DHLController extends Controller
{

    public function test()
    {
        $start = microtime(true);

        $testOrder = Order::find(107610);
        DHLJob::dispatch($testOrder);

        echo PHP_EOL . 'Executed in ' . (microtime(true) - $start) . ' seconds.' . PHP_EOL;
        exit();
    }
}
