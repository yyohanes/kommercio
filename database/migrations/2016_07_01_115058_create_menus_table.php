<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menus', function(Blueprint $table){
            $table->increments('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        //Create first menu
        $menu = \Kommercio\Models\CMS\Menu::create([
            'name' => 'Main Menu',
            'description' => 'Menu responsible for Main navigation.'
        ]);

        Schema::create('menu_items', function(Blueprint $table){
            $table->increments('id');
            $table->integer('menu_id')->unsigned();
            $table->integer('parent_id')->unsigned()->nullable();
            $table->boolean('active')->default(true);
            $table->dateTime('active_date_from')->nullable();
            $table->dateTime('active_date_to')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('menu_items')->onDelete('SET NULL');
            $table->foreign('menu_id')->references('id')->on('menus')->onDelete('CASCADE');
        });

        Schema::create('menu_item_translations', function(Blueprint $table){
            $table->increments('id');
            $table->integer('menu_item_id')->unsigned();
            $table->string('name');
            $table->string('url')->nullable();
            $table->text('data')->nullable();
            $table->string('locale')->index();

            $table->unique(['menu_item_id', 'locale']);
            $table->foreign('menu_item_id')->references('id')->on('menu_items')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('menu_item_translations');
        Schema::drop('menu_items');
        Schema::drop('menus');
    }
}
