<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRedemptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('redemptions', function(Blueprint $table){
            $table->increments('id');
            $table->decimal('points', 14, 2);
            $table->integer('reward_id')->unsigned()->nullable();
            $table->integer('customer_id')->unsigned()->nullable();
            $table->integer('coupon_id')->unsigned()->nullable();

            $table->string('status')->nullable();
            $table->timestamps();

            $table->foreign('coupon_id')->references('id')->on('coupons');
            $table->foreign('reward_id')->references('id')->on('rewards');
            $table->foreign('customer_id')->references('id')->on('customers');
        });

        Schema::table('coupons', function(Blueprint $table){
            $table->string('type')->default(\Kommercio\Models\PriceRule\Coupon::TYPE_ONLINE)->after('coupon_code');
            $table->integer('cart_price_rule_id')->unsigned()->nullable()->change();
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
            $table->dropColumn('type');
        });

        Schema::drop('redemptions');
    }
}
