<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActiveLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('active_locations', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('identifier')->unique();
            $table->string('bill_identifier');
            $table->unsignedInteger('customer_id');
            $table->unsignedInteger('location_id');
            $table->unsignedInteger('transaction_notification_id')->nullable();
            $table->unsignedInteger('transaction_id')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers');

            $table->foreign('location_id')
                ->references('id')
                ->on('locations');

            $table->foreign('transaction_notification_id')
                ->references('id')
                ->on('transaction_notifications');

            $table->foreign('transaction_id')
                ->references('id')
                ->on('transactions');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('active_locations');
    }
}
