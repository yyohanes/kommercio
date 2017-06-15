<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductPriceRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('price_rules', function(Blueprint $table){
            $table->increments('id');
            $table->string('name')->nullable();
            $table->integer('product_id')->unsigned()->nullable();
            $table->integer('variation_id')->unsigned()->nullable();
            $table->decimal('price', 14, 2)->nullable();
            $table->decimal('modification', 14, 2)->nullable();
            $table->string('modification_type')->nullable();
            $table->string('currency')->nullable();
            $table->integer('store_id')->unsigned()->nullable();
            $table->boolean('active')->default(true);
            $table->dateTime('active_date_from')->nullable();
            $table->dateTime('active_date_to')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('CASCADE');
            $table->foreign('variation_id')->references('id')->on('products')->onDelete('CASCADE');
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('price_rules');
    }
}
