<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPostCategoryParent extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('post_categories', function(Blueprint $table){
            $table->integer('parent_id')->unsigned()->nullable()->after('id');
            $table->integer('sort_order')->default(0)->after('parent_id');

            $table->foreign('parent_id')->references('id')->on('post_categories')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('post_categories', function(Blueprint $table){
            $table->dropForeign('post_categories_parent_id_foreign');
            $table->dropColumn('sort_order');
            $table->dropColumn('parent_id');
        });
    }
}
