<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddShowCustomCityColumnAddress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('address_countries', function(Blueprint $table){
            $table->boolean('show_custom_city')->after('active')->default(false);
        });

        Schema::table('address_states', function(Blueprint $table){
            $table->boolean('show_custom_city')->after('active')->default(false);
        });

        Schema::table('address_cities', function(Blueprint $table){
            $table->boolean('show_custom_city')->after('active')->default(false);
        });

        Schema::table('address_districts', function(Blueprint $table){
            $table->boolean('show_custom_city')->after('active')->default(false);
        });

        Schema::table('address_areas', function(Blueprint $table){
            $table->boolean('show_custom_city')->after('active')->default(false);
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
            Schema::table('address_countries', function(Blueprint $table){
                $table->dropColumn('show_custom_city');
            });

            Schema::table('address_states', function(Blueprint $table){
                $table->dropColumn('show_custom_city');
            });

            Schema::table('address_cities', function(Blueprint $table){
                $table->dropColumn('show_custom_city');
            });

            Schema::table('address_districts', function(Blueprint $table){
                $table->dropColumn('show_custom_city');
            });

            Schema::table('address_areas', function(Blueprint $table){
                $table->dropColumn('show_custom_city');
            });
        });
    }
}
