<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IndexLineItemIdType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('line_items', function(Blueprint $table){
            $table->index(['line_item_id', 'line_item_type'], 'line_items_id_type_index');
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
            $table->dropIndex('line_items_id_type_index');
        });
    }
}
