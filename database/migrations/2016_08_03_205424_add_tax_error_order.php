<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTaxErrorOrder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Go through all tax line items
        $taxLineItems = \Kommercio\Models\Order\LineItem::where('line_item_type', 'tax')->get();
        foreach($taxLineItems as $taxLineItem)
        {
            $taxLineItem->tax_rate = $taxLineItem->tax->rate;
            $taxLineItem->save();
        }

        Schema::table('orders', function(Blueprint $table){
            $table->decimal('tax_error_total', 14, 2)->nullable()->after('tax_total');
        });

        //Process ALL orders
        $orders = \Kommercio\Models\Order\Order::checkout()->get();
        foreach($orders as $order){
            $order->calculateTaxError();
            $order->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function(Blueprint $table){
            $table->dropColumn('tax_error_total');
        });
    }
}
