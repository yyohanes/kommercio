<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateManufacturersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('manufacturers', function(Blueprint $table){
            $table->increments('id');
            $table->string('name');
            $table->string('slug');
            $table->timestamps();
        });

        Schema::table('products', function(Blueprint $table){
            $table->integer('manufacturer_id')->unsigned()->nullable();

            $table->foreign('manufacturer_id')->references('id')->on('manufacturers')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function(Blueprint $table){
            $table->dropForeign('products_manufacturer_id_foreign');

            $table->dropColumn('manufacturer_id');
        });

        Schema::drop('manufacturers');
    }
}
