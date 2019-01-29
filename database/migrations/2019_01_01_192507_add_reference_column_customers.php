<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReferenceColumnCustomers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function(Blueprint $table){
            $table->string('reference')->after('id');
            $table->unique('reference');
        });

        // Fill reference of existing customers
        $customers = Kommercio\Models\Customer::all();
        foreach ($customers as $customer) {
            $customer->update([
                'reference' => $customers->generateReference(),
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
        Schema::table('customers', function(Blueprint $table){
            $table->dropColumn('reference');
        });
    }
}
