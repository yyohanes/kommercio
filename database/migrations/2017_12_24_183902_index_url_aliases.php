<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IndexUrlAliases extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('url_aliases', function(Blueprint $table) {
            $table->index('internal_path');
            $table->index('external_path');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('url_aliases', function(Blueprint $table) {
            $table->dropIndex('url_aliases_internal_path_index');
            $table->dropIndex('url_aliases_external_path_index');
        });
    }
}
