<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reward_point_transactions', function(Blueprint $table){
            $table->bigIncrements('id');
            $table->integer('order_id')->unsigned()->nullable();
            $table->integer('customer_id')->unsigned();
            $table->decimal('amount', 14,2)->default(0);
            $table->string('type');
            $table->string('status');
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->text('data')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('SET NULL');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('CASCADE');

            $table->foreign('created_by')->references('id')->on('users')->onDelete('SET NULL');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('SET NULL');
        });

        Schema::table('customers', function(Blueprint $table){
            $table->decimal('reward_points', 14,2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reward_point_transactions');

        Schema::table('customers', function(Blueprint $table){
            $table->dropColumn('reward_points');
        });
    }
}
