<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayFacOwnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pay_fac_owners', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('identifier')->unique();
            $table->unsignedInteger('pay_fac_account_id');
            $table->string('country')->default('US');
            $table->string('state');
            $table->string('city');
            $table->string('zip');
            $table->string('address');
            $table->string('address_secondary')->nullable();
            $table->date('dob');
            $table->binary('ssn');
            $table->string('gender')->default('UNKNOWN');
            $table->string('last_name');
            $table->string('first_name');
            $table->string('title');
            $table->string('phone');
            $table->string('email');
            $table->boolean('primary')->default(true);
            $table->integer('percent_ownership');
            $table->timestamps();

            $table->foreign('pay_fac_account_id')
                ->references('id')
                ->on('pay_fac_accounts')
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
        Schema::dropIfExists('pay_fac_owners');
    }
}
