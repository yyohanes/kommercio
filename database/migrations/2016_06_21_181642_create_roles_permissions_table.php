<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRolesPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function(Blueprint $table){
            $table->increments('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('role_permissions', function(Blueprint $table){
            $table->integer('role_id')->unsigned();
            $table->string('permission');

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('CASCADE');
        });

        Schema::create('role_user', function(Blueprint $table){
            $table->integer('role_id')->unsigned();
            $table->integer('user_id')->unsigned();

            $table->foreign('role_id')->references('id')->on('roles')->onDelete('CASCADE');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
        });

        Schema::create('store_user', function(Blueprint $table){
            $table->integer('store_id')->unsigned();
            $table->integer('user_id')->unsigned();

            $table->foreign('store_id')->references('id')->on('stores')->onDelete('CASCADE');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
        });

        $superAdminRole = \Kommercio\Models\Role\Role::create([
            'name' => 'Super Administrator'
        ]);
        $user = \Kommercio\Models\User::findOrFail(1);
        $user->roles()->attach($superAdminRole);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('store_user');
        Schema::drop('role_user');
        Schema::drop('role_permissions');
        Schema::drop('roles');
    }
}
