<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Kommercio\Facades\ProjectHelper;

class ReprocessTaxLineItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //REProcess ALL orders
        $orders = \Kommercio\Models\Order\Order::checkout()->get();
        if(!ProjectHelper::getSiteConfig('tax_reprocessed', false)) {
            foreach ($orders as $order) {
                $subtotal = 0;

                foreach ($order->lineItems as $lineItem) {
                    if ($lineItem->taxable) {
                        $subtotal += $lineItem->calculateTotal(false);
                    }
                }

                foreach ($order->getTaxLineItems() as $taxLineItem) {
                    ProjectHelper::saveSiteConfig('tax_reprocessed', true);

                    $base = PriceFormatter::round($subtotal * $taxLineItem->tax_rate / 100);
                    $taxLineItem->net_price = $taxLineItem->base_price;
                    $taxLineItem->base_price = $base;
                    $taxLineItem->save();
                }

                $order->calculateTaxError();
                $order->save();
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
        ProjectHelper::saveSiteConfig('tax_reprocessed', null);

        $orders = \Kommercio\Models\Order\Order::checkout()->get();
        foreach ($orders as $order) {
            foreach ($order->getTaxLineItems() as $taxLineItem) {
                $taxLineItem->base_price = $taxLineItem->net_price;
                $taxLineItem->save();
            }
        }
    }
}
