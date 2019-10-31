<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRefundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('transaction_id');
            $table->unsignedInteger('status_id');
            $table->integer('total');
            $table->string('pos_refund_id');
            $table->string('payment_refund_id')->nullable();
            $table->timestamps();

            $table->foreign('transaction_id')
                ->references('id')
                ->on('transactions');

            $table->foreign('status_id')
                ->references('id')
                ->on('refund_statuses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('refunds');
    }
}
