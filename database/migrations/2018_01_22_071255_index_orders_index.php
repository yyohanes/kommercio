<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IndexOrdersIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders_index', function(Blueprint $table) {
            $table->index('delivery_date');
            $table->index('getShippingLineItem_line_item_id');
            $table->index('shippingProfile_postal_code');
            $table->index('shippingProfile_address_2');
            $table->index('shippingProfile_phone_number');
            $table->index('shippingProfile_email');
            $table->index('status');
            $table->index('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders_index', function(Blueprint $table) {
            $table->dropIndex('orders_index_delivery_date_index');
            $table->dropIndex('orders_index_getShippingLineItem_line_item_id_index');
            $table->dropIndex('orders_index_shippingProfile_postal_code_index');
            $table->dropIndex('orders_index_shippingProfile_address_2_index');
            $table->dropIndex('orders_index_shippingProfile_phone_number_index');
            $table->dropIndex('orders_index_shippingProfile_email_index');
            $table->dropIndex('orders_index_status_index');
            $table->dropIndex('orders_index_ip_address_index');
        });
    }
}
