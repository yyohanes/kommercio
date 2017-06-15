<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pages', function(Blueprint $table){
            $table->increments('id');
            $table->integer('parent_id')->nullable()->unsigned();
            $table->boolean('active')->default(true);
            $table->dateTime('active_date_from')->nullable();
            $table->dateTime('active_date_to')->nullable();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('pages')->onDelete('SET NULL');
        });

        Schema::create('page_translations', function(Blueprint $table){
            $table->increments('id');
            $table->integer('page_id')->unsigned();
            $table->string('name');
            $table->string('slug');
            $table->text('body')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('locale')->index();

            $table->unique(['page_id', 'locale']);
            $table->foreign('page_id')->references('id')->on('pages')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('page_translations');
        Schema::dropIfExists('pages');
    }
}
