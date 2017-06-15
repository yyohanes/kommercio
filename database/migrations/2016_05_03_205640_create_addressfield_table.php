<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddressfieldTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('address_countries', function(Blueprint $table){
            $table->increments('id');
            $table->string('name');
            $table->string('iso_code');
            $table->string('country_code');
            $table->integer('sort_order')->default(0);
            $table->boolean('has_descendant')->default(true);
            $table->boolean('active')->default(true);
        });

        Schema::create('address_states', function(Blueprint $table){
            $table->increments('id');
            $table->integer('country_id')->unsigned();
            $table->string('name');
            $table->string('iso_code')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('has_descendant')->default(true);
            $table->boolean('active')->default(true);

            $table->foreign('country_id')->references('id')->on('address_countries')->onDelete('CASCADE');
        });

        Schema::create('address_cities', function(Blueprint $table){
            $table->increments('id');
            $table->integer('state_id')->unsigned();
            $table->string('name');
            $table->string('iso_code')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('has_descendant')->default(true);
            $table->boolean('active')->default(true);

            $table->foreign('state_id')->references('id')->on('address_states')->onDelete('CASCADE');
        });

        Schema::create('address_districts', function(Blueprint $table){
            $table->increments('id');
            $table->integer('city_id')->unsigned();
            $table->string('name');
            $table->integer('sort_order')->default(0);
            $table->boolean('has_descendant')->default(true);
            $table->boolean('active')->default(true);

            $table->foreign('city_id')->references('id')->on('address_cities')->onDelete('CASCADE');
        });

        Schema::create('address_areas', function(Blueprint $table){
            $table->increments('id');
            $table->integer('district_id')->unsigned();
            $table->string('name');
            $table->integer('sort_order')->default(0);
            $table->boolean('active')->default(true);

            $table->foreign('district_id')->references('id')->on('address_districts')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('address_areas');
        Schema::dropIfExists('address_districts');
        Schema::dropIfExists('address_cities');
        Schema::dropIfExists('address_states');
        Schema::dropIfExists('address_countries');
    }
}
