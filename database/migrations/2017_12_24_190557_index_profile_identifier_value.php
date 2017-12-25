<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IndexProfileIdentifierValue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('profile_details', function(Blueprint $table) {
            $table->index([
                'identifier',
                'value',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('profile_details', function(Blueprint $table) {
            $table->dropIndex('profile_details_identifier_value_index');
        });
    }
}
