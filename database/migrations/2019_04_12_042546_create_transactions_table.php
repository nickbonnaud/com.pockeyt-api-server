<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('customer_id');
            $table->unsignedInteger('business_id');
            $table->unsignedInteger('status_id');
            $table->uuid('identifier')->unique();
            $table->string('payment_transaction_id')->nullable();
            $table->string('pos_transaction_id');
            $table->string('employee_id')->nullable();
            $table->integer('tax');
            $table->integer('tip')->default(0);
            $table->integer('net_sales');
            $table->integer('total');
            $table->integer('partial_payment')->default(0);
            $table->boolean('locked')->default(true);
            $table->timestamp('bill_created_at');
            $table->timestamps();

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers');

            $table->foreign('business_id')
                ->references('id')
                ->on('businesses');

            $table->foreign('status_id')
                ->references('id')
                ->on('transaction_statuses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
