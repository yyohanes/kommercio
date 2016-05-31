<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function(Blueprint $table){
            $table->increments('id');
            $table->integer('payment_method_id')->nullable()->unsigned();
            $table->integer('order_id')->nullable()->unsigned();
            $table->decimal('amount', 14, 2);
            $table->string('currency', 10);
            $table->string('status', 100)->default(\Kommercio\Models\Order\Payment::STATUS_SUCCESS);
            $table->text('notes')->nullable();
            $table->longText('data')->nullable();
            $table->timestamps();

            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('SET NULL');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('SET NULL');

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('SET NULL');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('payments');
    }
}
