<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentShippingManytomanyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_method_shipping_method', function(Blueprint $table) {
            $table->integer('payment_method_id')->unsigned();
            $table->integer('shipping_method_id')->unsigned();

            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('CASCADE');
            $table->foreign('shipping_method_id')->references('id')->on('shipping_methods')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('payment_method_shipping_method');
    }
}
