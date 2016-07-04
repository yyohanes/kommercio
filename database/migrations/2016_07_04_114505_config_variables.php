<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConfigVariables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function(Blueprint $table){
            $table->text('data')->nullable()->after('notes');
        });

        Schema::create('config_variables', function(Blueprint $table){
            $table->string('key')->unique();
            $table->text('value')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('config_variables');

        if(Schema::hasColumn('orders', 'data')){
            Schema::table('orders', function(Blueprint $table){
                $table->dropColumn('data');
            });
        }
    }
}
