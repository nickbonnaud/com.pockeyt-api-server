<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCardCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('card_customers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('customer_account_id');
            $table->uuid('shopper_reference')->unique();
            $table->string('recurring_detail_reference')->nullable();
            $table->timestamps();

            $table->foreign('customer_account_id')
                ->references('id')
                ->on('customer_accounts')
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
        Schema::dropIfExists('card_customers');
    }
}
