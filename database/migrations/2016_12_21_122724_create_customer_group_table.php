<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_groups', function(Blueprint $table){
            $table->increments('id');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('customer_group_translations', function(Blueprint $table){
            $table->increments('id');
            $table->string('name');
            $table->integer('customer_group_id')->unsigned();
            $table->string('locale')->index();

            $table->unique(['customer_group_id', 'locale']);
            $table->foreign('customer_group_id')->references('id')->on('customer_groups')->onDelete('CASCADE');
        });

        Schema::create('customer_customer_group', function(Blueprint $table){
            $table->integer('customer_id')->unsigned();
            $table->integer('customer_group_id')->unsigned();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('CASCADE');
            $table->foreign('customer_group_id')->references('id')->on('customer_groups')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('customer_customer_group');
        Schema::drop('customer_group_translations');
        Schema::drop('customer_groups');
    }
}
