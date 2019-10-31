<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGeoAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('geo_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('identifier')->unique();
            $table->unsignedInteger('location_id');
            $table->string('lat');
            $table->string('lng');
            $table->integer('radius');
            $table->timestamps();

            $table->foreign('location_id')
                ->references('id')
                ->on('locations')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('geo_accounts');
    }
}
