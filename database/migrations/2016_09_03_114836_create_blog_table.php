<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBlogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function(Blueprint $table){
            $table->increments('id');
            $table->boolean('active')->default(true);
            $table->dateTime('active_date_from')->nullable();
            $table->dateTime('active_date_to')->nullable();
            $table->timestamps();
        });

        Schema::create('post_translations', function(Blueprint $table){
            $table->increments('id');
            $table->integer('post_id')->unsigned();
            $table->string('name');
            $table->string('slug');
            $table->text('body')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('locale')->index();

            $table->unique(['post_id', 'locale']);
            $table->foreign('post_id')->references('id')->on('posts')->onDelete('CASCADE');
        });

        Schema::create('post_categories', function(Blueprint $table){
            $table->increments('id');
            $table->timestamps();
        });

        Schema::create('post_category_translations', function(Blueprint $table){
            $table->increments('id');
            $table->integer('post_category_id')->unsigned();
            $table->string('name');
            $table->string('slug');
            $table->text('body')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('locale')->index();

            $table->unique(['post_category_id', 'locale']);
            $table->foreign('post_category_id')->references('id')->on('post_categories')->onDelete('CASCADE');
        });

        Schema::create('post_post_category', function(Blueprint $table){
            $table->integer('post_id')->unsigned();
            $table->integer('post_category_id')->unsigned();

            $table->foreign('post_id')->references('id')->on('posts')->onDelete('CASCADE');
            $table->foreign('post_category_id')->references('id')->on('post_categories')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('post_post_category');
        Schema::drop('post_category_translations');
        Schema::drop('post_categories');
        Schema::drop('post_translations');
        Schema::drop('posts');
    }
}
