<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRewardTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rewards', function(Blueprint $table){
            $table->increments('id');
            $table->string('type')->default(\Kommercio\Models\RewardPoint\Reward::TYPE_ONLINE_COUPON);
            $table->boolean('active')->default(true);
            $table->dateTime('active_date_from')->nullable();
            $table->dateTime('active_date_to')->nullable();
            $table->integer('cart_price_rule_id')->unsigned()->nullable();
            $table->decimal('points', 14, 2);
            $table->integer('store_id')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('cart_price_rule_id')->references('id')->on('cart_price_rules')->onDelete('SET NULL');
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('SET NULL');
        });

        Schema::create('reward_translations', function(Blueprint $table){
            $table->increments('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('reward_id')->unsigned();
            $table->string('locale')->index();

            $table->unique(['reward_id', 'locale']);
            $table->foreign('reward_id')->references('id')->on('rewards')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('reward_translations');
        Schema::drop('rewards');
    }
}
