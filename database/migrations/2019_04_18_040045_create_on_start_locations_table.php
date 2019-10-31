<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOnStartLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('on_start_locations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('customer_id');
            $table->unsignedInteger('region_id')->nullable();
            $table->unsignedInteger('location_id')->nullable();
            $table->string('lat');
            $table->string('lng');
            $table->boolean('beacon_start')->default(false);
            $table->timestamps();

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers');

            $table->foreign('region_id')
                ->references('id')
                ->on('regions');

            $table->foreign('location_id')
                ->references('id')
                ->on('locations');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('on_start_locations');
    }
}
