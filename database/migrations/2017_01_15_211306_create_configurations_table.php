<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConfigurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_configuration_groups', function(Blueprint $table){
            $table->increments('id');
            $table->string('slug');
            $table->timestamps();
        });

        Schema::create('product_configuration_group_translations', function(Blueprint $table){
            $table->increments('id');
            $table->string('name');
            $table->string('locale')->index();
            $table->integer('product_configuration_group_id')->unsigned();

            $table->unique(['product_configuration_group_id', 'locale'], 'product_configuration_group_id_locale_unique');
            $table->foreign('product_configuration_group_id', 'product_configuration_group_id_translation')->references('id')->on('product_configuration_groups')->onDelete('CASCADE');
        });

        Schema::create('product_configurations', function(Blueprint $table){
            $table->increments('id');
            $table->string('slug');
            $table->string('type');
            $table->text('data');
            $table->timestamps();
        });

        Schema::create('product_configuration_translations', function(Blueprint $table){
            $table->increments('id');
            $table->string('name');
            $table->string('locale')->index();
            $table->integer('product_configuration_id')->unsigned();

            $table->unique(['product_configuration_id', 'locale'], 'product_configuration_id_locale_unique');
            $table->foreign('product_configuration_id', 'product_configuration_id_translation')->references('id')->on('product_configurations')->onDelete('CASCADE');
        });

        Schema::create('product_configuration_product_configuration_group', function(Blueprint $table){
            $table->integer('product_configuration_id')->unsigned();
            $table->integer('product_configuration_group_id')->unsigned();
            $table->boolean('required')->default(false);
            $table->integer('sort_order')->default(0);

            $table->foreign('product_configuration_id', 'configuration_product_configuration_foreign')->references('id')->on('product_configurations')->onDelete('CASCADE');
            $table->foreign('product_configuration_group_id', 'configuration_product_configuration_group_foreign')->references('id')->on('product_configuration_groups')->onDelete('CASCADE');
        });

        Schema::create('product_configuration_group_product', function(Blueprint $table){
            $table->integer('product_configuration_group_id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->integer('sort_order')->default(0);

            $table->foreign('product_configuration_group_id', 'product_configuration_group_product_foreign')->references('id')->on('product_configuration_groups')->onDelete('CASCADE');
            $table->foreign('product_id')->references('id')->on('products');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('product_configuration_group_product');
        Schema::drop('product_configuration_product_configuration_group');
        Schema::drop('product_configuration_translations');
        Schema::drop('product_configurations');
        Schema::drop('product_configuration_group_translations');
        Schema::drop('product_configuration_groups');
    }
}
