<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCurrencyColumnProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_details', function(Blueprint $table){
            $table->string('currency', 4)->default(\Kommercio\Facades\CurrencyHelper::getCurrentCurrency()['iso']);
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
            $table->dropColumn('currency');
        });
    }
}
