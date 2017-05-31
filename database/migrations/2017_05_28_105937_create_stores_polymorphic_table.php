<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoresPolymorphicTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_attachables', function(Blueprint $table){
            $table->integer('store_id')->unsigned();
            $table->morphs('store_attachable', 'store_attachable_morph_index');

            $table->foreign('store_id', 'FK_store_attachables_stores')->references('id')->on('stores')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('store_attachables');
    }
}
