<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stores', function(Blueprint $table){
            $table->increments('id');
            $table->string('name');
            $table->string('code')->unique();
            $table->string('type');
            $table->boolean('default')->default(false);
            $table->timestamps();
        });

        $defaultStore = \Kommercio\Models\Store::create([
            'name' => 'Online Store',
            'type' => \Kommercio\Models\Store::TYPE_ONLINE,
            'default' => TRUE,
            'code' => 'OL'
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stores');
    }
}
