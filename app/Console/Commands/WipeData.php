<?php

namespace Kommercio\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Kommercio\Models\File;
use Kommercio\Models\Manufacturer;
use Kommercio\Models\Media;
use Kommercio\Models\Order\Order;
use Kommercio\Models\Product;

class WipeData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wipe-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Wipe Kommercio data';

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
        $type = $this->choice('Choose what data you want to wipe out', ['Order', 'Product', 'Manufacturer', 'Unused Files']);

        switch($type){
            case 'Order':
                $orders = Order::all();
                $bar = $this->output->createProgressBar($orders->count());

                foreach($orders as $order){
                    $order->delete();
                    $bar->advance();
                }

                DB::statement('SET FOREIGN_KEY_CHECKS=0');

                DB::table('line_items')->truncate();
                $this->info('Table line_items truncated');

                DB::table('orders')->truncate();
                $this->info('Table orders truncated');

                DB::table('payments')->truncate();
                $this->info('Table payments truncated');

                if(Schema::hasTable('delivery_orders')){
                    DB::table('delivery_orders')->truncate();
                    $this->info('Table delivery_orders truncated');
                }

                DB::statement('SET FOREIGN_KEY_CHECKS=1');

                $bar->finish();

                break;
            case 'Product':
                $products = Product::productEntity()->get();
                $bar = $this->output->createProgressBar($products->count());

                foreach($products as $product){
                    $product->forceDelete();
                    $bar->advance();
                }

                DB::statement('SET FOREIGN_KEY_CHECKS=0');

                DB::table('products')->truncate();
                $this->info('Table products truncated');

                DB::table('product_translations')->truncate();
                $this->info('Table product_translations truncated');

                DB::table('product_details')->truncate();
                $this->info('Table product_details truncated');

                DB::table('product_product_attribute')->truncate();
                $this->info('Table product_product_attribute truncated');

                DB::table('product_product_composite')->truncate();
                $this->info('Table product_product_composite truncated');

                DB::table('product_product_feature')->truncate();
                $this->info('Table product_product_feature truncated');

                DB::table('product_warehouse')->truncate();
                $this->info('Table product_warehouse truncated');

                DB::table('product_index')->truncate();
                $this->info('Table product_index truncated');

                DB::table('product_index_price')->truncate();
                $this->info('Table product_index_price truncated');

                DB::table('product_children')->truncate();
                $this->info('Table product_children truncated');

                DB::table('category_product')->truncate();
                $this->info('Table category_product truncated');

                DB::statement('SET FOREIGN_KEY_CHECKS=1');

                $bar->finish();

                break;
            case 'Manufacturer':
                $manufacturers = Manufacturer::all();
                $bar = $this->output->createProgressBar($manufacturers->count());

                foreach($manufacturers as $manufacturer){
                    $manufacturer->delete();
                    $bar->advance();

                    DB::statement('SET FOREIGN_KEY_CHECKS=0');

                    DB::table('manufacturers')->truncate();
                    $this->info('Table manufacturers truncated');

                    DB::statement('SET FOREIGN_KEY_CHECKS=1');
                }

                $bar->finish();

                break;
            case 'Unused Files':
                $files = Media::all();
                $bar = $this->output->createProgressBar($files->count());

                foreach($files as $file){
                    $mediaAttachables = DB::table('media_attachables')->where('media_id', $file->id)->get();

                    $count = 0;

                    foreach($mediaAttachables as $mediaAttachable){
                        $model = $mediaAttachable->media_attachable_type;
                        if($model::where('id', $mediaAttachable->media_attachable_id)->count() > 0){
                            $count += 1;
                        }
                    }

                    if($count < 1){
                        $file->delete();
                    }
                    $bar->advance();
                }
                break;
            default:
                $this->error('Unknown selection.');
                break;
        }
    }
}
