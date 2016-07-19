<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBlocksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('blocks', function(Blueprint $table){
            $table->increments('id');
            $table->string('machine_name');
            $table->string('type', 100)->default(\Kommercio\Models\Blocks\Block::TYPE_STATIC);
            $table->timestamps();
        });

        Schema::create('block_translations', function(Blueprint $table){
            $table->increments('id');
            $table->integer('block_id')->unsigned();
            $table->string('name');
            $table->text('body')->nullable();
            $table->string('locale')->index();

            $table->unique(['block_id', 'locale']);
            $table->foreign('block_id')->references('id')->on('blocks')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('block_translations');
        Schema::drop('blocks');
    }
}
