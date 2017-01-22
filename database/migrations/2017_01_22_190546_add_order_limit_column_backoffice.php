<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderLimitColumnBackoffice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_limits', function(Blueprint $table){
            $table->boolean('backoffice')->default(TRUE)->after('limit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_limits', function(Blueprint $table){
            $table->dropColumn('backoffice');
        });
    }
}
