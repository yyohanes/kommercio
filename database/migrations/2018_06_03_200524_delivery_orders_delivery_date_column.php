<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeliveryOrdersDeliveryDateColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delivery_orders', function(Blueprint $table){
            $table->timestamp('delivery_date')->nullable()->after('reference');
        });

        $deliveryOrders = \Kommercio\Models\Order\DeliveryOrder\DeliveryOrder::all();
        foreach ($deliveryOrders as $deliveryOrder) {
            $order = $deliveryOrder->order;

            if ($order && $order->delivery_date) {
                $deliveryOrder->delivery_date = $order->delivery_date;
                $deliveryOrder->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('delivery_orders', function(Blueprint $table){
            $table->dropColumn('delivery_date');
        });
    }
}
