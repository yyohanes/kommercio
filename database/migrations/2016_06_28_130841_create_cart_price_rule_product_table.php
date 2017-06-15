<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCartPriceRuleProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cart_price_rule_product', function(Blueprint $table){
            $table->integer('product_id')->unsigned();
            $table->integer('cart_price_rule_id')->unsigned();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('CASCADE');
            $table->foreign('cart_price_rule_id')->references('id')->on('cart_price_rules')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cart_price_rule_product');
    }
}
