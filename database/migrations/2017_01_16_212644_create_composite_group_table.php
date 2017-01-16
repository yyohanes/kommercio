<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompositeGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_composite_groups', function(Blueprint $table){
            $table->increments('id');
            $table->string('name');
            $table->string('slug');
            $table->timestamps();
        });

        Schema::create('product_composite_product_composite_group', function(Blueprint $table){
            $table->integer('product_composite_id')->unsigned();
            $table->integer('product_composite_group_id')->unsigned();
            $table->integer('sort_order')->default(0);

            $table->foreign('product_composite_id', 'composite_product_composite_foreign')->references('id')->on('product_composites')->onDelete('CASCADE');
            $table->foreign('product_composite_group_id', 'composite_product_composite_group_foreign')->references('id')->on('product_composite_groups')->onDelete('CASCADE');
        });

        Schema::create('product_composite_group_product', function(Blueprint $table){
            $table->integer('product_composite_group_id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->integer('sort_order')->default(0);

            $table->foreign('product_composite_group_id', 'product_composite_group_product_foreign')->references('id')->on('product_composite_groups')->onDelete('CASCADE');
            $table->foreign('product_id')->references('id')->on('products');
        });

        Schema::table('product_composites', function(Blueprint $table){
            $table->decimal('minimum', 10, 2)->default(0);
            $table->decimal('maximum', 10, 2)->default(0);
        });

        Schema::create('product_product_composite', function(Blueprint $table){
            $table->integer('product_composite_id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->integer('sort_order')->default(0);

            $table->foreign('product_id', 'ppc_product_id_foreign')->references('id')->on('products')->onDelete('CASCADE');
            $table->foreign('product_composite_id', 'ppc_product_composite_id_foreign')->references('id')->on('product_composites')->onDelete('CASCADE');
        });

        Schema::create('product_category_product_composite', function(Blueprint $table){
            $table->integer('product_composite_id')->unsigned();
            $table->integer('product_category_id')->unsigned();

            $table->foreign('product_category_id', 'pcpc_product_category_id_foreign')->references('id')->on('product_categories')->onDelete('CASCADE');
            $table->foreign('product_composite_id', 'pcpc_product_composite_id_foreign')->references('id')->on('product_composites')->onDelete('CASCADE');
        });

        //Migrate old composites
        if(Schema::hasTable('product_product_composite_configuration')){
            $qb = \Illuminate\Support\Facades\DB::table('product_product_composite_configuration')->join('product_composite_configurations AS CC', 'CC.id', '=', 'product_composite_configuration_id');
            foreach($qb->get() as $row){
                \Illuminate\Support\Facades\DB::table('product_product_composite')->insert([
                    'product_composite_id' => $row->product_composite_id,
                    'product_id' => $row->product_id,
                    'sort_order' => $row->sort_order
                ]);
            }

            Schema::drop('product_product_composite_configuration');
        }

        if(Schema::hasTable('product_composite_configurations')){
            $qb = \Illuminate\Support\Facades\DB::table('product_composite_configurations')->groupBy('product_id');
            foreach($qb->get() as $row){
                $productCompositeGroup = new \Kommercio\Models\Product\Composite\ProductCompositeGroup();
                $productCompositeGroup->save();

                $productCompositeGroup->products()->attach($row->product_id);

                $compositeQb = \Illuminate\Support\Facades\DB::table('product_composite_configurations')->where('product_id', $row->product_id);
                foreach($compositeQb->get() as $compositeRow){
                    $composite = \Kommercio\Models\Product\Composite\ProductComposite::find($compositeRow->product_composite_id);

                    if($composite){
                        $composite->update([
                            'minimum' => $compositeRow->minimum,
                            'maximum' => $compositeRow->maximum,
                        ]);

                        $productCompositeGroup->composites()->attach($composite, [
                            'sort_order' => $compositeRow->sort_order
                        ]);
                    }
                }
            }

            Schema::drop('product_composite_configurations');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_composites', function(Blueprint $table){
            $table->dropColumn('minimum');
            $table->dropColumn('maximum');
        });

        Schema::drop('product_category_product_composite');
        Schema::drop('product_product_composite');
        Schema::drop('product_composite_group_product');
        Schema::drop('product_composite_product_composite_group');
        Schema::drop('product_composite_groups');
    }
}
