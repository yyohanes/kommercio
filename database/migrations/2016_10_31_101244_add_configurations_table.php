<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddConfigurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_composites', function(Blueprint $table){
            $table->increments('id');
            $table->string('name');
            $table->string('slug');
            $table->timestamps();
        });

        Schema::create('product_composite_translations', function(Blueprint $table){
            $table->increments('id');
            $table->string('label')->nullable();
            $table->integer('product_composite_id')->unsigned();
            $table->string('locale')->index();
            $table->timestamps();

            $table->unique(['product_composite_id', 'locale'], 'composite_translations_composite_id_locale_unique');
            $table->foreign('product_composite_id')->references('id')->on('product_composites')->onDelete('CASCADE');
        });

        Schema::create('product_composite_configurations', function(Blueprint $table){
            $table->increments('id');
            $table->integer('product_id')->unsigned();
            $table->integer('product_composite_id')->unsigned();
            $table->decimal('minimum', 10, 2)->default(0);
            $table->decimal('maximum', 10, 2)->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('product_id', 'pcc_product_id_foreign')->references('id')->on('products')->onDelete('CASCADE');
            $table->foreign('product_composite_id', 'pcc_product_composite_id_foreign')->references('id')->on('product_composites')->onDelete('CASCADE');
        });

        Schema::create('product_product_composite_configuration', function(Blueprint $table){
            $table->integer('product_composite_configuration_id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->integer('sort_order')->default(0);

            $table->foreign('product_id', 'ppcc_product_id_foreign')->references('id')->on('products')->onDelete('CASCADE');
            $table->foreign('product_composite_configuration_id', 'ppcc_product_composite_configuration_id_foreign')->references('id')->on('product_composite_configurations')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('product_product_composite_configuration');
        Schema::drop('product_composite_configurations');
        Schema::drop('product_composite_translations');
        Schema::drop('product_composites');
    }
}
