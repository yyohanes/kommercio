<?php

namespace Kommercio\Console\Commands;

use Illuminate\Console\Command;
use Kommercio\Models\Order\Order;

class ScoutIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scout-index';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Index these with Scout';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $type = $this->choice('Choose what data you want to index', ['Order']);

        switch($type) {
            case 'Order':
                $perBatch = 100;
                $allRecords = Order::checkout()->count();
                $totalBatch = ceil($allRecords / $perBatch);

                $bar = $this->output->createProgressBar($allRecords);

                for ($i = 0; $i < $totalBatch; $i += 1) {
                    $orders = Order::checkout()
                        ->skip($i * $perBatch)
                        ->take($perBatch)
                        ->get();

                    foreach ($orders as $order) {
                        $order->searchable();
                        foreach ($order->allLineItems as $lineItem) {
                            $lineItem->searchable();
                        }

                        $bar->advance();
                    }
                }
            default:
                $this->error('Unknown selection.');
                break;
        }
    }
}
