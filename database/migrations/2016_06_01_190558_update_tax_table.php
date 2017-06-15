<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTaxTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_details', function(Blueprint $table){
            if(Schema::hasColumn('product_details', 'tax_group_id')){
                $table->dropColumn('tax_group_id');
            }

            $table->boolean('taxable')->default(TRUE);
        });

        Schema::dropIfExists('product_detail_tax');

        Schema::create('tax_rules', function(Blueprint $table){
            $table->integer('tax_id')->unsigned();
            $table->morphs('tax_optionable');

            $table->foreign('tax_id')->references('id')->on('taxes')->onDelete('CASCADE');
        });

        Schema::table('line_items', function(Blueprint $table){
            $table->boolean('taxable')->default(FALSE)->after('total');
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
            $table->dropColumn('taxable');
        });

        Schema::dropIfExists('tax_rules');

        Schema::table('line_items', function(Blueprint $table){
            $table->dropColumn('taxable');
        });
    }
}
