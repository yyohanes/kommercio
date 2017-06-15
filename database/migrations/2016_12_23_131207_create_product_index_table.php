<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductIndexTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_index', function(Blueprint $table){
            $table->integer('root_product_id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->string('type');
            $table->integer('pivot')->nullable();
            $table->integer('value');
            $table->integer('store_id')->unsigned();

            $table->foreign('root_product_id')->references('id')->on('products')->onDelete('CASCADE');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('CASCADE');
        });

        Schema::create('product_index_price', function(Blueprint $table){
            $table->integer('root_product_id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->decimal('value', 14, 2);
            $table->string('currency');
            $table->integer('store_id')->unsigned();

            $table->foreign('root_product_id')->references('id')->on('products')->onDelete('CASCADE');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('CASCADE');
        });

        $products = \Kommercio\Models\Product::all();
        foreach($products as $product)
        {
            $product->saveToIndex();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_index');
        Schema::dropIfExists('product_index_price');
    }
}
