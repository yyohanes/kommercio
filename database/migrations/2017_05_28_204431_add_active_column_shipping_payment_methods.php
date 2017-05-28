<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddActiveColumnShippingPaymentMethods extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_methods', function(Blueprint $table){
            $table->boolean('active')->default(true);
        });

        Schema::table('shipping_methods', function(Blueprint $table){
            $table->boolean('active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_methods', function(Blueprint $table){
            $table->dropColumn('active');
        });

        Schema::table('shipping_methods', function(Blueprint $table){
            $table->dropColumn('active');
        });
    }
}
