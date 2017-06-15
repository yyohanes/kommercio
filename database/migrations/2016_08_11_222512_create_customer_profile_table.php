<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerProfileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_profile', function(Blueprint $table){
            $table->integer('customer_id')->unsigned();
            $table->integer('profile_id')->unsigned();
            $table->string('name')->nullable();
            $table->boolean('billing')->default(false);
            $table->boolean('shipping')->default(false);

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('CASCADE');
            $table->foreign('profile_id')->references('id')->on('profiles')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_profile');
    }
}
