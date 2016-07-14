<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTaxRateDiscountPercentLineitems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('line_items', function(Blueprint $table){
            $table->decimal('tax_rate', 5, 2)->nullable()->after('tax_total');
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
            $table->dropColumn('tax_rate');
        });
    }
}
