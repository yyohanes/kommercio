<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProductAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_attributes', function(Blueprint $table){
            $table->increments('id');
            $table->integer('sort_order')->default(0);
        });

        Schema::create('product_attribute_translations', function(Blueprint $table){
            $table->increments('id');
            $table->integer('product_attribute_id')->unsigned();
            $table->string('name');
            $table->string('slug');
            $table->string('locale')->index();

            $table->unique(['product_attribute_id', 'locale'], 'product_attribute_locale_unique');
            $table->foreign('product_attribute_id', 'product_attribute_translation_foreign')->references('id')->on('product_attributes')->onDelete('CASCADE');
        });

        Schema::create('product_attribute_values', function(Blueprint $table){
            $table->increments('id');
            $table->integer('product_attribute_id')->unsigned();
            $table->integer('sort_order')->default(0);

            $table->foreign('product_attribute_id', 'product_attribute_value_foreign')->references('id')->on('product_attributes')->onDelete('CASCADE');
        });

        Schema::create('product_attribute_value_translations', function(Blueprint $table){
            $table->increments('id');
            $table->integer('product_attribute_value_id')->unsigned();
            $table->string('name');
            $table->string('slug');
            $table->string('locale')->index();

            $table->unique(['product_attribute_value_id', 'locale'], 'product_attribute_value_locale_unique');
            $table->foreign('product_attribute_value_id', 'product_attribute_value_translation_foreign')->references('id')->on('product_attribute_values')->onDelete('CASCADE');
        });

        Schema::create('product_product_attribute', function(Blueprint $table){
            $table->integer('product_id')->unsigned();
            $table->integer('product_attribute_id')->unsigned();
            $table->integer('product_attribute_value_id')->unsigned();

            $table->unique(['product_id', 'product_attribute_id', 'product_attribute_value_id'], 'product_product_attribute_value_unique');

            $table->foreign('product_id', 'ppa_product_id_foreign')->references('id')->on('products')->onDelete('CASCADE');
            $table->foreign('product_attribute_id', 'ppa_product_attribute_id_foreign')->references('id')->on('product_attributes');
            $table->foreign('product_attribute_value_id', 'ppa_product_attribute_value_id_foreign')->references('id')->on('product_attribute_values');
        });

        Schema::table('products', function(Blueprint $table){
            $table->integer('parent_id')->unsigned()->nullable();

            $table->foreign('parent_id')->references('id')->on('products')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_product_attribute');
        Schema::dropIfExists('product_attribute_value_translations');
        Schema::dropIfExists('product_attribute_values');
        Schema::dropIfExists('product_attribute_translations');
        Schema::dropIfExists('product_attributes');

        Schema::table('products', function(Blueprint $table){
            $table->dropForeign('products_parent_id_foreign');
            $table->dropColumn('parent_id');
        });
    }
}
