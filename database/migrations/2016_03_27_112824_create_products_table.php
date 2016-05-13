<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function(Blueprint $table){
            $table->increments('id');
            $table->string('sku')->unique();
            $table->string('type')->default(\Kommercio\Models\Product::TYPE_DEFAULT);
            $table->string('combination_type')->default(\Kommercio\Models\Product::COMBINATION_TYPE_SINGLE);
            $table->integer('default_category_id')->unsigned()->nullable();
            $table->decimal('width', 6,2)->nullable()->default(null);
            $table->decimal('length', 6,2)->nullable()->default(null);
            $table->decimal('depth', 6,2)->nullable()->default(null);
            $table->decimal('weight', 6,2)->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('default_category_id')->references('id')->on('product_categories')->onDelete('SET NULL');
        });

        Schema::create('product_details', function(Blueprint $table){
            $table->increments('id');
            $table->integer('product_id')->unsigned();
            $table->integer('store_id')->unsigned();
            $table->integer('tax_group_id')->unsigned();
            $table->decimal('retail_price', 14,2);
            $table->boolean('available')->default(true);
            $table->dateTime('available_date_from')->nullable();
            $table->dateTime('available_date_to')->nullable();
            $table->string('visibility', 20)->default(\Kommercio\Models\ProductDetail::VISIBILITY_EVERYWHERE);
            $table->boolean('active')->default(true);
            $table->dateTime('active_date_from')->nullable();
            $table->dateTime('active_date_to')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'store_id']);
            $table->foreign('product_id')->references('id')->on('products')->onDelete('CASCADE');
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('CASCADE');

            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('SET NULL');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('SET NULL');
        });

        Schema::create('product_children', function(Blueprint $table){
            $table->integer('product_id')->unsigned();
            $table->integer('child_id')->unsigned();
            $table->integer('sort_order')->default(0);

            $table->foreign('product_id')->references('id')->on('products')->onDelete('CASCADE');
            $table->foreign('child_id')->references('id')->on('products')->onDelete('CASCADE');
        });

        Schema::create('category_product', function(Blueprint $table){
            $table->integer('product_category_id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->integer('sort_order')->default(0);

            $table->foreign('product_category_id')->references('id')->on('product_categories')->onDelete('CASCADE');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('CASCADE');
        });

        Schema::create('product_translations', function(Blueprint $table){
            $table->increments('id');
            $table->integer('product_id')->unsigned();
            $table->string('name');
            $table->text('description_short')->nullable();
            $table->text('description')->nullable();
            $table->string('slug');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('locale')->index();

            $table->unique(['product_id', 'locale']);
            $table->foreign('product_id')->references('id')->on('products')->onDelete('CASCADE');

            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('SET NULL');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('SET NULL');
        });

        Schema::create('taxes', function(Blueprint $table){
            $table->increments('id');
            $table->string('name');
            $table->decimal('rate', 5,2);
            $table->integer('sort_order')->default(0);
            $table->string('type', 100)->default(0);
            $table->boolean('shipping')->default(FALSE);
            $table->timestamps();
        });

        Schema::create('product_detail_tax', function(Blueprint $table){
            $table->integer('product_detail_id')->unsigned();
            $table->integer('tax_id')->unsigned();

            $table->foreign('tax_id')->references('id')->on('taxes')->onDelete('CASCADE');
            $table->foreign('product_detail_id')->references('id')->on('product_details')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('product_translations');
        Schema::drop('product_detail_tax');
        Schema::drop('product_details');
        Schema::drop('product_children');
        Schema::drop('category_product');
        Schema::drop('taxes');
        Schema::drop('products');
    }
}
