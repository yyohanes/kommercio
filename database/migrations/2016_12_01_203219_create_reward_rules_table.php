<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRewardRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reward_rules', function(Blueprint $table){
            $table->increments('id');
            $table->string('name');
            $table->string('type');
            $table->decimal('reward', 14, 2)->default(0);
            $table->string('currency')->nullable();
            $table->timestamp('date_from')->nullable();
            $table->timestamp('date_to')->nullable();
            $table->integer('store_id')->unsigned()->nullable();
            $table->boolean('active')->default(TRUE);
            $table->integer('sort_order')->default(0);
            $table->text('data');
            $table->timestamps();

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
        Schema::drop('reward_rules');
    }
}
