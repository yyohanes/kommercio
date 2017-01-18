<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBookmarkTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookmark_types', function(Blueprint $table){
            $table->increments('id');
            $table->string('name');
            $table->string('slug');
            $table->boolean('default')->default(false);
            $table->timestamps();
        });

        Schema::create('bookmarks', function(Blueprint $table){
            $table->increments('id');
            $table->string('name');
            $table->integer('customer_id')->unsigned()->nullable();
            $table->integer('bookmark_type_id')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('bookmark_type_id')->references('id')->on('bookmark_types')->onDelete('SET NULL');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('SET NULL');
        });

        Schema::create('bookmark_product', function(Blueprint $table){
            $table->integer('product_id')->unsigned();
            $table->integer('bookmark_id')->unsigned();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('CASCADE');
            $table->foreign('bookmark_id')->references('id')->on('bookmarks')->onDelete('CASCADE');
        });

        //Default bookmark
        $bookmarkType = new \Kommercio\Models\Customer\BookmarkType([
            'name' => 'Favorite',
            'default' => true
        ]);
        $bookmarkType->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('bookmark_product');
        Schema::drop('bookmarks');
        Schema::drop('bookmark_types');
    }
}
