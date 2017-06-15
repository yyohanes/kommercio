<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBannersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('banner_groups', function(Blueprint $table){
            $table->increments('id');
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('banners', function(Blueprint $table){
            $table->increments('id');
            $table->integer('banner_group_id')->unsigned()->nullable();
            $table->boolean('active')->default(true);
            $table->dateTime('active_date_from')->nullable();
            $table->dateTime('active_date_to')->nullable();
            $table->text('data')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('banner_group_id')->references('id')->on('banner_groups')->onDelete('SET NULL');
        });

        Schema::create('banner_translations', function(Blueprint $table){
            $table->increments('id');
            $table->integer('banner_id')->unsigned();
            $table->string('name');
            $table->text('body')->nullable();
            $table->string('locale')->index();

            $table->unique(['banner_id', 'locale']);
            $table->foreign('banner_id')->references('id')->on('banners')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('banner_translations');
        Schema::dropIfExists('banners');
        Schema::dropIfExists('banner_groups');
    }
}
