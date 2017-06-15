<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConfigurationStorage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_configuration_product_configuration_group', function(Blueprint $table){
            $table->string('label')->nullable();
        });

        $productConfigurationGroups = \Kommercio\Models\Product\Configuration\ProductConfigurationGroup::all();
        foreach($productConfigurationGroups as $productConfigurationGroup){
            foreach($productConfigurationGroup->configurations as $configuration){
                $productConfigurationGroup->configurations()->updateExistingPivot($configuration->id, [
                    'label' => $configuration->name
                ]);
            }
        }

        Schema::create('line_item_product_configuration', function(Blueprint $table){
            $table->bigInteger('line_item_id')->unsigned();
            $table->integer('product_configuration_id')->unsigned()->nullable();
            $table->string('label');
            $table->string('type');
            $table->text('value')->nullable();

            $table->foreign('line_item_id')->references('id')->on('line_items')->onDelete('CASCADE');
            $table->foreign('product_configuration_id')->references('id')->on('product_configurations')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_configuration_product_configuration_group', function(Blueprint $table){
            $table->dropColumn('label');
        });

        Schema::dropIfExists('line_item_product_configuration');
    }
}
