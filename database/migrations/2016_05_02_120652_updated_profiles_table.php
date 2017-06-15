<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatedProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('user_profile_details');

        Schema::create('profiles', function(Blueprint $table){
            $table->increments('id');
            $table->string('name')->nullable();
            $table->morphs('profileable');
            $table->timestamps();
        });

        Schema::create('profile_details', function(Blueprint $table){
            $table->increments('id');
            $table->integer('profile_id')->unsigned();
            $table->string('identifier')->index();
            $table->string('value')->nullable();

            $table->unique(['profile_id', 'identifier']);
            $table->foreign('profile_id')->references('id')->on('profiles')->onDelete('CASCADE');
        });

        Schema::create('customers', function(Blueprint $table){
            $table->increments('id');
            $table->integer('store_id')->unsigned()->nullable();
            $table->integer('user_id')->unsigned()->nullable();
            $table->integer('profile_id')->unsigned()->nullable();
            $table->integer('shipping_profile_id')->unsigned()->nullable();
            $table->timestamps();
            $table->timestamp('last_active')->nullable();

            $table->foreign('store_id')->references('id')->on('stores')->onDelete('SET NULL');
            $table->foreign('profile_id')->references('id')->on('profiles')->onDelete('SET NULL');
            $table->foreign('shipping_profile_id')->references('id')->on('profiles')->onDelete('SET NULL');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('SET NULL');
        });

        Schema::table('users', function(Blueprint $table){
            $table->integer('profile_id')->after('remember_token')->unsigned()->nullable();
            $table->string('status', 10)->after('profile_id')->default(\Kommercio\Models\User::STATUS_ACTIVE);

            $table->foreign('profile_id')->references('id')->on('profiles')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $customersWithAccount = \Kommercio\Models\Customer::whereNotNull('user_id')->get();
        foreach($customersWithAccount as $customerWithAccount){
            $customerWithAccount->user->delete();
        }

        Schema::table('users', function(Blueprint $table){
            $table->dropForeign('users_profile_id_foreign');
            $table->dropColumn('profile_id');
            $table->dropColumn('status');
        });

        Schema::dropIfExists('customers');
        Schema::dropIfExists('profile_details');
        Schema::dropIfExists('profiles');
    }
}
