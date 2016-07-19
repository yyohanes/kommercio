<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSlugToMenuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('menus', function(Blueprint $table){
            $table->string('slug')->after('name');
        });

        //Create first menu
        $menu = \Kommercio\Models\CMS\Menu::create([
            'name' => 'Main Menu',
            'description' => 'Menu responsible for Main navigation.'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('menus', function(Blueprint $table){
            $table->dropColumn('slug');
        });
    }
}
