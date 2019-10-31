<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnassignedTransactionPurchasedItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unassigned_transaction_purchased_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('unassigned_transaction_id');
            $table->unsignedInteger('item_id');
            $table->timestamps();

            $table->foreign('unassigned_transaction_id')
                ->references('id')
                ->on('unassigned_transactions')
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
        Schema::dropIfExists('unassigned_transaction_purchased_items');
    }
}
