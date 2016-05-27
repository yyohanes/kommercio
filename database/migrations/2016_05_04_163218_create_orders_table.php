<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_methods', function(Blueprint $table){
            $table->increments('id');
            $table->string('class');
            $table->integer('sort_order')->default(0);
        });

        Schema::create('payment_method_translations', function(Blueprint $table){
            $table->increments('id');
            $table->integer('payment_method_id')->unsigned();
            $table->string('name');
            $table->text('message')->nullable();
            $table->string('locale', 10)->index();

            $table->unique(['payment_method_id', 'locale']);
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('CASCADE');
        });

        Schema::create('shipping_methods', function(Blueprint $table){
            $table->increments('id');
            $table->string('class');
            $table->integer('sort_order')->default(0);
        });

        Schema::create('shipping_method_translations', function(Blueprint $table){
            $table->increments('id');
            $table->integer('shipping_method_id')->unsigned();
            $table->string('name');
            $table->text('message')->nullable();
            $table->string('locale', 10)->index();

            $table->unique(['shipping_method_id', 'locale']);
            $table->foreign('shipping_method_id')->references('id')->on('shipping_methods')->onDelete('CASCADE');
        });

        Schema::create('orders', function(Blueprint $table){
            $table->increments('id');
            $table->string('reference');
            $table->integer('customer_id')->unsigned()->nullable();
            $table->integer('store_id')->unsigned()->nullable();
            $table->integer('billing_profile_id')->unsigned()->nullable();
            $table->integer('shipping_profile_id')->unsigned()->nullable();
            $table->timestamp('delivery_date')->nullable();
            $table->timestamp('checkout_at')->nullable();
            $table->decimal('subtotal', 14,2);
            $table->decimal('discount_total', 14,2)->nullable();
            $table->decimal('shipping_total', 14,2)->nullable();
            $table->decimal('tax_total', 14,2)->nullable();
            $table->decimal('additional_total', 14,2)->nullable();
            $table->decimal('total', 14,2);
            $table->string('currency', 10);
            $table->decimal('conversion_rate', 14, 6);
            $table->string('status')->default(\Kommercio\Models\Order\Order::STATUS_PENDING);
            $table->integer('payment_method_id')->unsigned()->nullable();
            $table->integer('shipping_method_id')->unsigned()->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('store_id')->references('id')->on('stores')->onDelete('SET NULL');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('SET NULL');
            $table->foreign('billing_profile_id')->references('id')->on('profiles');
            $table->foreign('shipping_profile_id')->references('id')->on('profiles');

            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('SET NULL');
            $table->foreign('shipping_method_id')->references('id')->on('shipping_methods')->onDelete('SET NULL');

            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('SET NULL');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('SET NULL');
        });

        Schema::create('line_items', function(Blueprint $table){
            $table->integer('id');
            $table->integer('order_id')->unsigned();
            $table->integer('line_item_id')->unsigned()->nullable();
            $table->string('line_item_type', 100);
            $table->string('name')->nullable();
            $table->decimal('base_price', 14,2)->nullable();
            $table->decimal('quantity', 14,2)->nullable();
            $table->decimal('net_price', 14,2)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('line_items');
        Schema::drop('orders');
        Schema::drop('shipping_method_translations');
        Schema::drop('shipping_methods');
        Schema::drop('payment_method_translations');
        Schema::drop('payment_methods');
    }
}
