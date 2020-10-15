<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHelpTicketRepliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('help_ticket_replies', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('help_ticket_id');
            $table->text('message');
            $table->boolean('from_customer');
            $table->boolean('read')->default(false);
            $table->timestamps();

            $table->foreign('help_ticket_id')
                ->references('id')
                ->on('help_tickets')
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
        Schema::dropIfExists('help_ticket_replies');
    }
}
