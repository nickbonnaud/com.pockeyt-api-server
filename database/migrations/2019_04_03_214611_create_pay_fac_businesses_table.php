<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayFacBusinessesTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::create('pay_fac_businesses', function (Blueprint $table) {
      $table->increments('id');
      $table->uuid('identifier')->unique();
      $table->unsignedInteger('pay_fac_account_id');
      $table->string('ein')->nullable();
      $table->string('business_name');
      $table->string('country')->default('US');
      $table->string('state');
      $table->string('city');
      $table->string('zip');
      $table->string('address');
      $table->string('address_secondary')->nullable();
      $table->string('mcc')->nullable();
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
    Schema::dropIfExists('pay_fac_businesses');
  }
}
