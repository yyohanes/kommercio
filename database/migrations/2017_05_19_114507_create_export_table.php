<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('export_batches', function(Blueprint $table){
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('export_items', function(Blueprint $table){
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('status')->default(\Kommercio\Utility\Export\Item::STATUS_PENDING);
            $table->text('notes')->nullable();
            $table->integer('export_batch_id')->unsigned();
            $table->binary('data')->nullable();
            $table->timestamps();

            $table->foreign('export_batch_id')->references('id')->on('export_batches')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('export_items');
        Schema::drop('export_batches');
    }
}
