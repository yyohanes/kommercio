<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCartPriceRules extends Migration
{
    public function up()
    {
        Schema::create('cart_price_rules', function(Blueprint $table){
            $table->increments('id');
            $table->string('name')->nullable();
            $table->decimal('price', 14, 2)->nullable();
            $table->decimal('modification', 14, 2)->nullable();
            $table->string('modification_type')->nullable();
            $table->string('currency')->nullable();
            $table->integer('store_id')->unsigned()->nullable();
            $table->integer('customer_id')->unsigned()->nullable();
            $table->decimal('minimum_subtotal', 14, 2)->default(0);
            $table->integer('max_usage')->unsigned()->nullable();
            $table->integer('max_usage_per_customer')->unsigned()->nullable();
            $table->string('offer_type');
            $table->boolean('active')->default(true);
            $table->dateTime('active_date_from')->nullable();
            $table->dateTime('active_date_to')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('stores')->onDelete('SET NULL');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('SET NULL');
        });

        Schema::create('cart_price_rule_option_groups', function(Blueprint $table){
            $table->increments('id');
            $table->string('type');
            $table->integer('cart_price_rule_id')->unsigned();
            $table->integer('sort_order')->default(0);

            $table->foreign('cart_price_rule_id')->references('id')->on('cart_price_rules')->onDelete('CASCADE');
        });

        Schema::create('cart_price_rule_optionables', function(Blueprint $table){
            $table->integer('cart_price_rule_option_group_id')->unsigned();
            $table->morphs('cart_price_rule_optionable', 'cart_price_rule_optionable_index');

            $table->foreign('cart_price_rule_option_group_id', 'cart_price_rule_group_id_foreign')->references('id')->on('cart_price_rule_option_groups')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cart_price_rule_optionables');
        Schema::dropIfExists('cart_price_rule_option_groups');
        Schema::dropIfExists('cart_price_rules');
    }
}
