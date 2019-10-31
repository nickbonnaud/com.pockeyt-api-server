<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePosAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pos_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('identifier')->unique();
            $table->unsignedInteger('pos_account_status_id');
            $table->unsignedInteger('business_id');
            $table->string('type');
            $table->boolean('takes_tips');
            $table->boolean('allows_open_tickets');
            $table->timestamps();

            $table->foreign('pos_account_status_id')
                ->references('id')
                ->on('pos_account_statuses');

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
        Schema::dropIfExists('pos_accounts');
    }
}
