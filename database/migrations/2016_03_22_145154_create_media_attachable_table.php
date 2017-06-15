<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediaAttachableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media_attachables', function(Blueprint $table){
            $table->integer('media_id')->unsigned();
            $table->string('caption')->nullable();
            $table->morphs('media_attachable', 'media_attachable_morph_index');
            $table->string('type')->nullable();
            $table->string('locale')->nullable();

            $table->foreign('media_id', 'FK_media_attachables_files')->references('id')->on('files')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('media_attachables');
    }
}
