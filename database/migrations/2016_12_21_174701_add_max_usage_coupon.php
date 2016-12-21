<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMaxUsageCoupon extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coupons', function(Blueprint $table){
            $table->integer('max_usage')->nullable()->after('coupon_code');
        });

        $cartPriceRules = \Kommercio\Models\PriceRule\CartPriceRule::all();
        foreach($cartPriceRules as $cartPriceRule){
            foreach($cartPriceRule->coupons as $coupon){
                $coupon->update([
                    'max_usage' => $cartPriceRule->max_usage
                ]);
            }
        }

        Schema::table('cart_price_rules', function(Blueprint $table){
            $table->dropColumn('max_usage');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('coupons', function(Blueprint $table){
            $table->dropColumn('max_usage');
        });

        Schema::table('cart_price_rules', function(Blueprint $table){
            $table->integer('max_usage')->nullable();
        });
    }
}
