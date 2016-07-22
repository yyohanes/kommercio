<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewColumnProduct extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_details', function(Blueprint $table){
            $table->boolean('new')->default(false);
            $table->dateTime('new_date_from')->nullable();
            $table->dateTime('new_date_to')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_details', function(Blueprint $table){
            $table->dropColumn('new');
            $table->dropColumn('new_date_from');
            $table->dropColumn('new_date_to');
        });
    }
}
