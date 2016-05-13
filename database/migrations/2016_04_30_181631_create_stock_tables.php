<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStockTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warehouses', function(Blueprint $table){
            $table->increments('id');
            $table->string('name');
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('store_warehouse', function(Blueprint $table){
            $table->integer('store_id')->unsigned();
            $table->integer('warehouse_id')->unsigned();
            $table->integer('sort_order')->default(0);

            $table->foreign('store_id')->references('id')->on('stores')->onDelete('CASCADE');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('CASCADE');
        });

        Schema::create('product_warehouse', function(Blueprint $table){
            $table->integer('product_id')->unsigned();
            $table->integer('warehouse_id')->unsigned();
            $table->decimal('stock', 10, 2)->default(0);

            $table->foreign('product_id')->references('id')->on('products')->onDelete('CASCADE');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('CASCADE');
        });

        Schema::table('product_details', function(Blueprint $table){
            $table->boolean('manage_stock')->default(false);
        });

        //Create default warehouse
        $warehouse = \Kommercio\Models\Warehouse::create([
            'name' => 'Default Warehouse'
        ]);

        $stores = \Kommercio\Models\Store::all();

        $warehouse->stores()->sync($stores->pluck('id')->all());
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('product_warehouse');
        Schema::drop('store_warehouse');
        Schema::drop('warehouses');

        Schema::table('product_details', function(Blueprint $table){
            $table->dropColumn('manage_stock');
        });
    }
}
