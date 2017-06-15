<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCouponTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coupons', function(Blueprint $table){
            $table->increments('id');
            $table->string('coupon_code');
            $table->integer('cart_price_rule_id')->unsigned();
            $table->integer('customer_id')->unsigned()->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->text('data');
            $table->timestamps();

            $table->foreign('cart_price_rule_id')->references('id')->on('cart_price_rules')->onDelete('CASCADE');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('SET NULL');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('SET NULL');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('SET NULL');
        });

        //Migrate all coupons
        $cartRules = \Kommercio\Models\PriceRule\CartPriceRule::all();
        foreach($cartRules as $cartRule){
            if(!empty($cartRule->coupon_code)){
                $coupon = new \Kommercio\Models\PriceRule\Coupon([
                    'coupon_code' => $cartRule->coupon_code
                ]);
                $coupon->cartPriceRule()->associate($cartRule);
                $coupon->save();

                $couponLineItems = \Kommercio\Models\Order\LineItem::lineItemType('coupon')->where('line_item_id', $cartRule->id)->get();
                foreach($couponLineItems as $couponLineItem){
                    $couponLineItem->line_item_id = $coupon->id;
                    $couponLineItem->save();
                }
            }
        }

        if (Schema::hasColumn('cart_price_rules', 'coupon_code')) {
            Schema::table('cart_price_rules', function(Blueprint $table){
                $table->dropColumn('coupon_code');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasColumn('cart_price_rules', 'coupon_code')) {
            Schema::table('cart_price_rules', function(Blueprint $table){
                $table->string('coupon_code')->nullable();
            });
        }

        //Migrate back coupons to cart price rule
        $coupons = \Kommercio\Models\PriceRule\Coupon::all();
        foreach($coupons as $coupon){
            if($coupon->cartPriceRule){
                $coupon->cartPriceRule->coupon_code = $coupon->coupon_code;
                $coupon->cartPriceRule->save();

                $couponLineItems = \Kommercio\Models\Order\LineItem::lineItemType('coupon')->where('line_item_id', $coupon->id)->get();
                foreach($couponLineItems as $couponLineItem){
                    $couponLineItem->line_item_id = $coupon->cartPriceRule->id;
                    $couponLineItem->save();
                }
            }
        }

        Schema::dropIfExists('coupons');
    }
}
