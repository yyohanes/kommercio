<?php

namespace Kommercio\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Kommercio\Jobs\Index\OrderJob;
use Kommercio\Models\Order\Order;

class KommercioIndex extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kommercio-index {type} {--from_date=} {--to_date=}';

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
        $type = $this->argument('type');
        $fromDate = $this->option('from_date') ? new Carbon($this->option('from_date')) : null;
        $toDate = $this->option('to_date') ? new Carbon($this->option('to_date')) : null;

        switch(strtolower($type)) {
            case 'order':
                $perBatch = 25;
                $qb = Order::checkout();
                if ($fromDate) {
                    $qb->whereRaw('DATE_FORMAT(checkout_at, \'%Y-%m-%d\') >= ?', [$fromDate->format('Y-m-d')]);
                }
                if ($toDate) {
                    $qb->whereRaw('DATE_FORMAT(checkout_at, \'%Y-%m-%d\') <= ?', [$toDate->format('Y-m-d')]);
                }

                $allRecords = $qb->count();
                $this->info(sprintf('%d order (s) to be indexed', $allRecords));

                $totalBatch = ceil($allRecords / $perBatch);

                $bar = $this->output->createProgressBar($allRecords);

                for ($i = 0; $i < $totalBatch; $i += 1) {
                    $orders = $qb
                        ->skip($i * $perBatch)
                        ->take($perBatch)
                        ->get();

                    foreach ($orders as $order) {
                        $job = new OrderJob($order);

                        try {
                            $job->handle();
                        } catch (\Throwable $e) {
                            $this->error(sprintf('Error indexing order #%s', $order->id));
                        }

                        $bar->advance();
                    }
                }
                break;
            default:
                $this->error('Unknown selection.');
                break;
        }
    }
}
