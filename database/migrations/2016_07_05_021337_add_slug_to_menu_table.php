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

        $menus = \Kommercio\Models\CMS\Menu::all();
        foreach($menus as $menu){
            $menu->save();
        }
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
