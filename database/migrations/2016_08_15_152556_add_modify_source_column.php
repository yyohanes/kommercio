<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddModifySourceColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cart_price_rules', function(Blueprint $table){
            $table->integer('modification_source')->after('offer_type')->default(\Kommercio\Models\PriceRule\CartPriceRule::MODIFICATION_SOURCE_BASE);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cart_price_rules', function(Blueprint $table){
            $table->dropColumn('modification_source');
        });
    }
}
