<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGalleryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('galleries', function(Blueprint $table){
            $table->increments('id');
            $table->boolean('active')->default(true);
            $table->dateTime('active_date_from')->nullable();
            $table->dateTime('active_date_to')->nullable();
            $table->timestamps();
        });

        Schema::create('gallery_translations', function(Blueprint $table){
            $table->increments('id');
            $table->integer('gallery_id')->unsigned();
            $table->string('name');
            $table->string('slug');
            $table->text('body')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('locale')->index();

            $table->unique(['gallery_id', 'locale']);
            $table->foreign('gallery_id')->references('id')->on('galleries')->onDelete('CASCADE');
        });

        Schema::create('gallery_categories', function(Blueprint $table){
            $table->increments('id');
            $table->integer('parent_id')->unsigned()->nullable();
            $table->integer('sort_order')->default(0);

            $table->foreign('parent_id')->references('id')->on('gallery_categories')->onDelete('SET NULL');
            $table->timestamps();
        });

        Schema::create('gallery_category_translations', function(Blueprint $table){
            $table->increments('id');
            $table->integer('gallery_category_id')->unsigned();
            $table->string('name');
            $table->string('slug');
            $table->text('body')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('locale')->index();

            $table->unique(['gallery_category_id', 'locale']);
            $table->foreign('gallery_category_id')->references('id')->on('gallery_categories')->onDelete('CASCADE');
        });

        Schema::create('gallery_gallery_category', function(Blueprint $table){
            $table->integer('gallery_id')->unsigned();
            $table->integer('gallery_category_id')->unsigned();

            $table->foreign('gallery_id')->references('id')->on('galleries')->onDelete('CASCADE');
            $table->foreign('gallery_category_id')->references('id')->on('gallery_categories')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gallery_gallery_category');
        Schema::dropIfExists('gallery_category_translations');
        Schema::dropIfExists('gallery_categories');
        Schema::dropIfExists('gallery_translations');
        Schema::dropIfExists('galleries');
    }
}
