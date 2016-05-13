<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_categories', function(Blueprint $table){
            $table->increments('id');
            $table->integer('parent_id')->nullable()->unsigned();
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('product_categories')->onDelete('SET NULL');
        });

        Schema::create('product_category_translations', function(Blueprint $table){
            $table->increments('id');
            $table->integer('product_category_id')->unsigned();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('slug');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('locale')->index();

            $table->unique(['product_category_id', 'locale']);
            $table->foreign('product_category_id')->references('id')->on('product_categories')->onDelete('CASCADE');

            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('SET NULL');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('product_category_translations');
        Schema::drop('product_categories');
    }
}
