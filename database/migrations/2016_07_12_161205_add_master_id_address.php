<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMasterIdAddress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('address_countries', function(Blueprint $table){
            $table->integer('master_id')->unique()->nullable()->after('id');
        });

        Schema::table('address_states', function(Blueprint $table){
            $table->integer('master_id')->unique()->nullable()->after('id');
        });

        Schema::table('address_cities', function(Blueprint $table){
            $table->integer('master_id')->unique()->nullable()->after('id');
        });

        Schema::table('address_districts', function(Blueprint $table){
            $table->integer('master_id')->unique()->nullable()->after('id');
        });

        Schema::table('address_areas', function(Blueprint $table){
            $table->integer('master_id')->unique()->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('address_countries', function(Blueprint $table){
            $table->dropColumn('master_id');
        });

        Schema::table('address_states', function(Blueprint $table){
            $table->dropColumn('master_id');
        });

        Schema::table('address_cities', function(Blueprint $table){
            $table->dropColumn('master_id');
        });

        Schema::table('address_districts', function(Blueprint $table){
            $table->dropColumn('master_id');
        });

        Schema::table('address_areas', function(Blueprint $table){
            $table->dropColumn('master_id');
        });
    }
}
