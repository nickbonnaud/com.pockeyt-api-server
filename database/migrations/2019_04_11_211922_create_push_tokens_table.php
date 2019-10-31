<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePushTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('push_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('customer_id');
            $table->string('device');
            $table->string('token');
            $table->timestamps();

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
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
        Schema::dropIfExists('push_tokens');
    }
}
