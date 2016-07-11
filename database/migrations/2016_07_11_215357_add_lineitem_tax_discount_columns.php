<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLineitemTaxDiscountColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('line_items', function(Blueprint $table){
            $table->decimal('tax_total', 14, 2)->nullable()->after('net_price');
            $table->decimal('discount_total', 14, 2)->nullable()->after('net_price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('line_items', function(Blueprint $table){
            $table->dropColumn('tax_total');
            $table->dropColumn('discount_total');
        });
    }
}
