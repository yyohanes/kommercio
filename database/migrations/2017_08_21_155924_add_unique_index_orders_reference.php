<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUniqueIndexOrdersReference extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            // Update currently empty reference with ID as surrogate
            \Illuminate\Support\Facades\DB::table(with(new \Kommercio\Models\Order\Order())->getTable())
                ->where('reference', '')->orWhereNull('reference')
                ->update([
                'reference' => \Illuminate\Support\Facades\DB::raw('`id`')
            ]);

            // Get duplicated references and rename
            $duplicatedOrders = \Kommercio\Models\Order\Order::select(\Illuminate\Support\Facades\DB::raw('reference, COUNT(id) as count'))->groupBy('reference')
                ->havingRaw('COUNT(id) > 1')
                ->get();

            foreach($duplicatedOrders as $duplicatedOrder) {
                $duplicates = \Kommercio\Models\Order\Order::where('reference', $duplicatedOrder->reference)->get();

                foreach ($duplicates as $idx => $duplicate) {
                    if ($idx > 0) {
                        $duplicate->timestamps = false;
                        $duplicate->update([
                            'reference' => $duplicate->reference.' ('.$idx.')'
                        ]);
                    }
                }
            }

            Schema::table('orders', function(Blueprint $table) {
                $table->unique('reference');
            });
        } catch(\Exception $e) {
            \Illuminate\Support\Facades\DB::rollback();

            throw $e;
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function(Blueprint $table) {
            $table->dropUnique('orders_reference_unique');
        });
    }
}
