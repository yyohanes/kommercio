<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PaymentHistoryLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('logs', function(Blueprint $table){
            $table->text('value')->after('tag')->nullable();
            $table->text('data')->after('notes')->nullable();
        });

        $payments = \Kommercio\Models\Order\Payment::all();
        $paymentStatusArray = \Kommercio\Models\Order\Payment::getStatusOptions();

        foreach($payments as $payment){
            $history = $payment->getData('history', []);

            if(is_array($history)){
                foreach($history as $historyLog){
                    $log = $payment->recordStatusChange(array_search($historyLog['status'], $paymentStatusArray), $historyLog['by'], (!empty($history['notes'])?$history['notes']:null));

                    if(!empty($history['at'])){
                        $date = \Carbon\Carbon::parse($history['at']);
                        $log->setCreatedAt($date);
                        $log->setUpdatedAt($date);
                        $log->save();
                    }
                }
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
        \Kommercio\Models\Log::whereTag('payment.update')->delete();

        Schema::table('logs', function(Blueprint $table){
            $table->dropColumn('value');
            $table->dropColumn('data');
        });
    }
}
