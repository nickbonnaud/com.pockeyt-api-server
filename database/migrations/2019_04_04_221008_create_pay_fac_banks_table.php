<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayFacBanksTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('pay_fac_banks', function (Blueprint $table) {
      $table->increments('id');
      $table->uuid('identifier')->unique();
      $table->unsignedInteger('pay_fac_account_id');
      $table->string('country')->default('US');
      $table->string('state');
      $table->string('city');
      $table->string('zip');
      $table->string('address');
      $table->string('address_secondary')->nullable();
      $table->string('first_name');
      $table->string('last_name');
      $table->binary('routing_number');
      $table->binary('account_number');
      $table->string('bank_country')->default('US');
      $table->string('currency')->default('USD');
      $table->string('account_type');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('pay_fac_banks');
  }
}
