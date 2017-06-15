<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFlatRateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipping_flat_rates', function(Blueprint $table){
            $table->increments('id');
            $table->integer('address_id')->unsigned();
            $table->string('address_type', 10);
            $table->decimal('price', 14, 2)->nullable();
            $table->string('currency');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shipping_flat_rates');
    }
}
