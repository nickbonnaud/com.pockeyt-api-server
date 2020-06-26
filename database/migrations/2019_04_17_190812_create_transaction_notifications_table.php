<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('transaction_id');
            $table->string('last');
            $table->boolean('exit_sent')->default(false);
            $table->timestamp('time_exit_sent')->nullable();
            $table->boolean('bill_closed_sent')->default(false);
            $table->timestamp('time_bill_closed_sent')->nullable();
            $table->boolean('auto_pay_sent')->default(false);
            $table->timestamp('time_auto_pay_sent')->nullable();
            $table->boolean('fix_bill_sent')->default(false);
            $table->timestamp('time_fix_bill_sent')->nullable();
            $table->integer('number_times_fix_bill_sent')->default(0);
            $table->timestamps();

            $table->foreign('transaction_id')
                ->references('id')
                ->on('transactions')
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
        Schema::dropIfExists('transaction_notifications');
    }
}
