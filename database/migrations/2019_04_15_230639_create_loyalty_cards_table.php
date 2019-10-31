<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoyaltyCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loyalty_cards', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('customer_id');
            $table->unsignedInteger('loyalty_program_id');
            $table->integer('points');
            $table->integer('outstanding_rewards')->default(0);
            $table->integer('total_rewards_earned')->default(0);
            $table->timestamps();

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers');

            $table->foreign('loyalty_program_id')
                ->references('id')
                ->on('loyalty_programs')
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
        Schema::dropIfExists('loyalty_cards');
    }
}
