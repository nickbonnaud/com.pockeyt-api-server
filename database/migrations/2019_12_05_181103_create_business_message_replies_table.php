<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessMessageRepliesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('business_message_replies', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('identifier')->unique();
            $table->unsignedInteger('business_message_id');
            $table->text('body');
            $table->boolean('sent_by_business');
            $table->boolean('read')->default(false);
            $table->boolean('read_by_admin')->default(false);
            $table->timestamps();

            $table->foreign('business_message_id')
                ->references('id')
                ->on('business_messages')
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
        Schema::dropIfExists('business_message_replies');
    }
}
