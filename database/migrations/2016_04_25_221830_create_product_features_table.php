<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductFeaturesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_features', function(Blueprint $table){
            $table->increments('id');
            $table->integer('sort_order')->default(0);
        });

        Schema::create('product_feature_translations', function(Blueprint $table){
            $table->increments('id');
            $table->integer('product_feature_id')->unsigned();
            $table->string('name');
            $table->string('slug');
            $table->string('locale')->index();

            $table->unique(['product_feature_id', 'locale'], 'product_feature_locale_unique');
            $table->foreign('product_feature_id', 'product_feature_translation_foreign')->references('id')->on('product_features')->onDelete('CASCADE');
        });

        Schema::create('product_feature_values', function(Blueprint $table){
            $table->increments('id');
            $table->integer('product_feature_id')->unsigned();
            $table->boolean('custom')->default(false);
            $table->integer('sort_order')->default(0);

            $table->foreign('product_feature_id', 'product_feature_value_foreign')->references('id')->on('product_features')->onDelete('CASCADE');
        });

        Schema::create('product_feature_value_translations', function(Blueprint $table){
            $table->increments('id');
            $table->integer('product_feature_value_id')->unsigned();
            $table->string('name');
            $table->string('slug');
            $table->string('locale')->index();

            $table->unique(['product_feature_value_id', 'locale'], 'product_feature_value_locale_unique');
            $table->foreign('product_feature_value_id', 'product_feature_value_translation_foreign')->references('id')->on('product_feature_values')->onDelete('CASCADE');
        });

        Schema::create('product_product_feature', function(Blueprint $table){
            $table->integer('product_id')->unsigned();
            $table->integer('product_feature_id')->unsigned();
            $table->integer('product_feature_value_id')->unsigned();

            $table->unique(['product_id', 'product_feature_id', 'product_feature_value_id'], 'product_product_feature_value_unique');

            $table->foreign('product_id', 'ppf_product_id_foreign')->references('id')->on('products')->onDelete('CASCADE');
            $table->foreign('product_feature_id', 'ppf_product_feature_id_foreign')->references('id')->on('product_features');
            $table->foreign('product_feature_value_id', 'ppf_product_feature_value_id_foreign')->references('id')->on('product_feature_values');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('product_product_feature');
        Schema::drop('product_feature_value_translations');
        Schema::drop('product_feature_values');
        Schema::drop('product_feature_translations');
        Schema::drop('product_features');
    }
}
