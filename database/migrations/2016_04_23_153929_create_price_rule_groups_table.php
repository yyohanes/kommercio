<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePriceRuleGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('price_rule_option_groups', function(Blueprint $table){
            $table->increments('id');
            $table->integer('price_rule_id')->unsigned();
            $table->integer('sort_order')->default(0);

            $table->foreign('price_rule_id')->references('id')->on('price_rules')->onDelete('CASCADE');
        });

        Schema::create('price_rule_optionables', function(Blueprint $table){
            $table->integer('price_rule_option_group_id')->unsigned();
            $table->morphs('price_rule_optionable', 'price_rule_optionable_index');

            $table->foreign('price_rule_option_group_id', 'price_rule_group_id_foreign')->references('id')->on('price_rule_option_groups')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('price_rule_optionables');
        Schema::drop('price_rule_option_groups');
    }
}
