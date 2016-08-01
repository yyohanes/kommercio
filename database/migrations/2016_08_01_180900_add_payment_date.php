<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPaymentDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function(Blueprint $table) {
            $table->timestamp('payment_date')->nullable();
        });

        $payments = \Kommercio\Models\Order\Payment::all();
        foreach($payments as $payment){
            if(empty($payment->payment_date)){
                $payment->payment_date = $payment->created_at;
                $payment->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function(Blueprint $table) {
            $table->dropColumn('payment_date');
        });
    }
}
