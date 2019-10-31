<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayFacAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pay_fac_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('identifier')->unique();
            $table->unsignedInteger('account_id');
            $table->string('entity_type');
            $table->string('account_code')->nullable();
            $table->timestamps();

            $table->foreign('account_id')
                ->references('id')
                ->on('accounts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pay_fac_accounts');
    }
}
