<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('import_batches', function(Blueprint $table){
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('import_items', function(Blueprint $table){
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('status')->default(\Kommercio\Utility\Import\Item::STATUS_PENDING);
            $table->text('notes')->nullable();
            $table->integer('import_batch_id')->unsigned();
            $table->timestamps();

            $table->foreign('import_batch_id')->references('id')->on('import_batches')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('import_items');
        Schema::dropIfExists('import_batches');
    }
}
