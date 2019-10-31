<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoyaltyProgramsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loyalty_programs', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('identifier')->unique();
            $table->unsignedInteger('business_id');
            $table->integer('purchases_required')->nullable();
            $table->integer('amount_required')->nullable();
            $table->string('reward');
            $table->integer('total_rewards_earned')->default(0);
            $table->integer('outstanding_rewards')->default(0);
            $table->timestamps();

            $table->foreign('business_id')
                ->references('id')
                ->on('businesses')
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
        Schema::dropIfExists('loyalty_programs');
    }
}
