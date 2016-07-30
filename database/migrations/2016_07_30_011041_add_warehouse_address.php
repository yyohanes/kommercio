<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWarehouseAddress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('warehouses', function(Blueprint $table){
            $table->integer('area_id')->after('name')->unsigned()->nullable();
            $table->integer('district_id')->after('name')->unsigned()->nullable();
            $table->integer('city_id')->after('name')->unsigned()->nullable();
            $table->integer('state_id')->after('name')->unsigned()->nullable();
            $table->integer('country_id')->after('name')->unsigned()->nullable();
            $table->string('postal_code')->after('name')->nullable();
            $table->string('address_2')->after('name')->nullable();
            $table->string('address_1')->after('name')->nullable();

            $table->foreign('country_id')->references('id')->on('address_countries')->onDelete('SET NULL');
            $table->foreign('state_id')->references('id')->on('address_states')->onDelete('SET NULL');
            $table->foreign('city_id')->references('id')->on('address_cities')->onDelete('SET NULL');
            $table->foreign('district_id')->references('id')->on('address_districts')->onDelete('SET NULL');
            $table->foreign('area_id')->references('id')->on('address_areas')->onDelete('SET NULL');

            if(Schema::hasColumn('warehouses', 'address')) {
                $table->dropColumn('address');
            }
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
            $table->dropForeign('warehouses_country_id_foreign');
            $table->dropForeign('warehouses_state_id_foreign');
            $table->dropForeign('warehouses_city_id_foreign');
            $table->dropForeign('warehouses_district_id_foreign');
            $table->dropForeign('warehouses_area_id_foreign');

            $table->dropColumn('postal_code');
            $table->dropColumn('country_id');
            $table->dropColumn('state_id');
            $table->dropColumn('city_id');
            $table->dropColumn('district_id');
            $table->dropColumn('area_id');
            $table->dropColumn('address_1');
            $table->dropColumn('address_2');
        });
    }
}
