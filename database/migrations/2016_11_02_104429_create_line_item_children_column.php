<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLineItemChildrenColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('line_items', function(Blueprint $table){
            $table->bigIncrements('id')->change();
            $table->bigInteger('parent_id')->after('order_id')->unsigned()->nullable();
            $table->integer('product_composite_id')->unsigned()->nullable();
        });

        Schema::table('line_items', function(Blueprint $table){
            $table->foreign('product_composite_id')->references('id')->on('product_composites')->onDelete('SET NULL');
            $table->foreign('parent_id')->references('id')->on('line_items')->onDelete('CASCADE');
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
            $table->dropForeign('line_items_product_composite_id_foreign');
            $table->dropColumn('product_composite_id');
            $table->dropForeign('line_items_parent_id_foreign');
            $table->dropColumn('parent_id');
        });
    }
}
