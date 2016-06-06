<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderLimit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_limits', function(Blueprint $table){
            $table->increments('id');
            $table->string('type');
            $table->string('limit_type');
            $table->decimal('limit', 10,2);
            $table->timestamp('date_from')->nullable();
            $table->timestamp('date_to')->nullable();
            $table->integer('store_id')->unsigned()->nullable();
            $table->boolean('active')->default(TRUE);
            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('stores')->onDelete('CASCADE');
        });

        Schema::create('order_limitables', function(Blueprint $table){
            $table->integer('order_limit_id')->unsigned();
            $table->morphs('order_limitable');

            $table->foreign('order_limit_id')->references('id')->on('order_limits')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('order_limitables');
        Schema::drop('order_limits');
    }
}
