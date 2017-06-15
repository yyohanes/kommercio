<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeliveryOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('delivery_orders', function(Blueprint $table){
            $table->increments('id');
            $table->string('reference');
            $table->integer('counter');
            $table->integer('order_id')->unsigned();
            $table->integer('store_id')->unsigned();
            $table->integer('customer_id')->unsigned();
            $table->integer('shipping_profile_id')->unsigned()->nullable();
            $table->decimal('total_quantity');
            $table->decimal('total_weight');
            $table->string('status')->default(\Kommercio\Models\Order\DeliveryOrder\DeliveryOrder::STATUS_PENDING);
            $table->text('notes')->nullable();
            $table->integer('shipping_method_id')->unsigned()->nullable();
            $table->text('data')->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('stores');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('CASCADE');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('CASCADE');
            $table->foreign('shipping_method_id')->references('id')->on('shipping_methods');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });

        Schema::create('delivery_order_items', function(Blueprint $table){
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->decimal('quantity');
            $table->decimal('price', 14, 2);
            $table->integer('product_id')->unsigned();
            $table->bigInteger('line_item_id')->unsigned()->nullable();
            $table->integer('delivery_order_id')->unsigned();
            $table->decimal('weight');
            $table->text('data')->nullable();
            $table->integer('sort_order')->nullable();

            $table->foreign('line_item_id')->references('id')->on('line_items')->onDelete('SET NULL');
            $table->foreign('delivery_order_id')->references('id')->on('delivery_orders')->onDelete('CASCADE');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delivery_order_items');
        Schema::dropIfExists('delivery_orders');
    }
}
