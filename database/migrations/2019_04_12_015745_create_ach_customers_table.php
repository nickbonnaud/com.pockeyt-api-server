<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAchCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ach_customers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('customer_account_id');
            $table->string('customer_url');
            $table->string('funding_source_url')->nullable();
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
        Schema::dropIfExists('ach_customers');
    }
}
