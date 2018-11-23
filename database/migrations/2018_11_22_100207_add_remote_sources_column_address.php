<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRemoteSourcesColumnAddress extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('address_countries', function(Blueprint $table){
            $table->boolean('use_remote_city')->after('show_custom_city')->default(false);
            $table->string('remote_id')->after('show_custom_city')->nullable();
            $table->index('remote_id');
            $table->string('remote_type')->after('remote_id')->nullable();
            $table->string('remote_source')->after('remote_type')->nullable();
        });

        Schema::table('address_states', function(Blueprint $table){
            $table->boolean('use_remote_city')->after('show_custom_city')->default(false);
            $table->string('remote_id')->after('show_custom_city')->nullable();
            $table->index('remote_id');
            $table->string('remote_type')->after('remote_id')->nullable();
            $table->string('remote_source')->after('remote_type')->nullable();
        });

        Schema::table('address_cities', function(Blueprint $table){
            $table->boolean('use_remote_city')->after('show_custom_city')->default(false);
            $table->string('remote_id')->after('show_custom_city')->nullable();
            $table->index('remote_id');
            $table->string('remote_type')->after('remote_id')->nullable();
            $table->string('remote_source')->after('remote_type')->nullable();
        });

        Schema::table('address_districts', function(Blueprint $table){
            $table->boolean('use_remote_city')->after('show_custom_city')->default(false);
            $table->string('remote_id')->after('show_custom_city')->nullable();
            $table->index('remote_id');
            $table->string('remote_type')->after('remote_id')->nullable();
            $table->string('remote_source')->after('remote_type')->nullable();
        });

        Schema::table('address_areas', function(Blueprint $table){
            $table->boolean('use_remote_city')->after('show_custom_city')->default(false);
            $table->string('remote_id')->after('show_custom_city')->nullable();
            $table->index('remote_id');
            $table->string('remote_type')->after('remote_id')->nullable();
            $table->string('remote_source')->after('remote_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('address_countries', function(Blueprint $table){
            $table->dropColumn('remote_id');
            $table->dropColumn('remote_type');
            $table->dropColumn('remote_source');
            $table->dropColumn('use_remote_city');
        });

        Schema::table('address_states', function(Blueprint $table){
            $table->dropColumn('remote_id');
            $table->dropColumn('remote_type');
            $table->dropColumn('remote_source');
            $table->dropColumn('use_remote_city');
        });

        Schema::table('address_cities', function(Blueprint $table){
            $table->dropColumn('remote_id');
            $table->dropColumn('remote_type');
            $table->dropColumn('remote_source');
            $table->dropColumn('use_remote_city');
        });

        Schema::table('address_districts', function(Blueprint $table){
            $table->dropColumn('remote_id');
            $table->dropColumn('remote_type');
            $table->dropColumn('remote_source');
            $table->dropColumn('use_remote_city');
        });

        Schema::table('address_areas', function(Blueprint $table){
            $table->dropColumn('remote_id');
            $table->dropColumn('remote_type');
            $table->dropColumn('remote_source');
            $table->dropColumn('use_remote_city');
        });
    }
}
