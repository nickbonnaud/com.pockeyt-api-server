<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnassignedTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unassigned_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('business_id');
            $table->unsignedInteger('customer_id')->nullable();
            $table->unsignedInteger('status_id')->nullable();
            $table->string('pos_transaction_id');
            $table->string('employee_id')->nullable();
            $table->integer('tax');
            $table->integer('net_sales');
            $table->integer('total');
            $table->integer('partial_payment')->default(0);
            $table->timestamps();

            $table->foreign('business_id')
                ->references('id')
                ->on('businesses');

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers');

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
        Schema::dropIfExists('unassigned_transactions');
    }
}
