<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CustomCityColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('warehouses', function(Blueprint $table){
            $table->string('custom_city')->after('area_id')->nullable();
        });

        Schema::table('stores', function(Blueprint $table){
            $table->string('custom_city')->after('area_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('warehouses', function(Blueprint $table){
            $table->dropColumn('custom_city');
        });

        Schema::table('stores', function(Blueprint $table){
            $table->dropColumn('custom_city');
        });
    }
}
