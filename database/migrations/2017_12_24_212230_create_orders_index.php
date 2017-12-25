<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders_index', function(Blueprint $table) {
            $table->bigInteger('id')->primary();
            $table->string('billingProfile_email')->nullable();
            $table->string('billingProfile_phone_number')->nullable();
            $table->string('billingProfile_address_1', 255)->nullable();
            $table->string('billingProfile_address_2', 255)->nullable();
            $table->string('billingProfile_postal_code')->nullable();
            $table->string('shippingProfile_email')->nullable();
            $table->string('shippingProfile_phone_number')->nullable();
            $table->string('shippingProfile_address_1', 255)->nullable();
            $table->string('shippingProfile_address_2', 255)->nullable();
            $table->string('shippingProfile_postal_code')->nullable();
            $table->string('getShippingLineItem_line_item_id')->nullable();
            $table->string('getSelectedShippingMethod')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('status')->nullable();
            $table->dateTime('checkout_at')->nullable();
            $table->dateTime('delivery_date')->nullable();
            $table->integer('store_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('orders_index');
    }
}
