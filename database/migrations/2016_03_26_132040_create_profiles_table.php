<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_profile_details', function(Blueprint $table){
            $table->integer('user_id')->unsigned();
            $table->string('identifier');
            $table->string('value');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
            $table->index('identifier');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_profile_details');
    }
}
