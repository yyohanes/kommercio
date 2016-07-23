<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCrossSellTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('related_products', function(Blueprint $table){
            $table->integer('product_id')->unsigned();
            $table->integer('target_id')->unsigned();
            $table->string('type');
            $table->integer('sort_order')->default(0);

            $table->foreign('product_id')->references('id')->on('products')->onDelete('CASCADE');
            $table->foreign('target_id')->references('id')->on('products')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('related_products');
    }
}
