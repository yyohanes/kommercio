<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function(Blueprint $table){
            $table->increments('id');
            $table->string('public_id')->unique();
            $table->string('reference');
            $table->string('counter');
            $table->integer('order_id')->unsigned();
            $table->integer('store_id')->unsigned();
            $table->decimal('total', 14, 2);
            $table->string('status')->default(\Kommercio\Models\Order\Invoice::STATUS_UNPAID);
            $table->timestamp('payment_date')->nullable();
            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('stores')->onDelete('CASCADE');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('CASCADE');
        });

        Schema::table('payments', function(Blueprint $table){
            $table->integer('invoice_id')->after('order_id')->unsigned()->nullable();

            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('SET NULL');
        });

        //Generate invoices from existing orders
        $orders = \Kommercio\Models\Order\Order::checkout()->orderBy('checkout_at', 'ASC')->get();
        foreach($orders as $order){
            if($order->invoices->count() < 1){
                $invoice = \Kommercio\Models\Order\Invoice::createInvoice($order, $order->checkout_at);

                $successfulPayment = null;
                foreach($order->payments()->orderBy('created_at', 'DESC')->get() as $payment){
                    if(!$successfulPayment && $payment->status == \Kommercio\Models\Order\Payment::STATUS_SUCCESS){
                        $successfulPayment = $payment;
                    }

                    $payment->invoice()->associate($invoice);
                    $payment->save();
                }

                if($successfulPayment && $order->getOutstandingAmount() <= 0){
                    $invoice->markAsPaid($successfulPayment->payment_date);
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
        Schema::table('payments', function(Blueprint $table){
            $table->dropForeign('payments_invoice_id_foreign');
            $table->dropColumn('invoice_id');
        });

        Schema::drop('invoices');
    }
}
