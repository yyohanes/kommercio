<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSameDayDeliveryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipping_same_day_delivery_configs', function(Blueprint $table){
            $table->increments('id');
            $table->integer('address_id')->unsigned();
            $table->string('address_type', 10);
            $table->json('data');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shipping_same_day_delivery_configs');
    }
}
