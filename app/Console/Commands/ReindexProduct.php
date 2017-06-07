<?php

namespace Kommercio\Console\Commands;

use Illuminate\Console\Command;
use Kommercio\Models\Product;

class ReindexProduct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reindex-product';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reindex product';

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
        $products = Product::productEntity()->get();
        $bar = $this->output->createProgressBar($products->count());

        foreach($products as $product){
            $product->saveToIndex();
            $bar->advance();
        }
    }
}
