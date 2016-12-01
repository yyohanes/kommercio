<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetDefaultOrderCurrency extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(count(\Kommercio\Facades\CurrencyHelper::getActiveCurrencies()) < 2){
            \Kommercio\Models\Order\Order::whereNull('currency')->orWhere('currency', '')->update([
                'currency' => \Kommercio\Facades\CurrencyHelper::getCurrentCurrency()['code']
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
