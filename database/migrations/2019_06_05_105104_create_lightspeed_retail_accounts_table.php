<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLightspeedRetailAccountsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('lightspeed_retail_accounts', function (Blueprint $table) {
      $table->increments('id');
      $table->uuid('identifier')->unique();
      $table->unsignedInteger('pos_account_id');
      $table->string('access_token');
      $table->string('account_id');
      $table->string('refresh_token');
      $table->string('expiry');
      $table->timestamps();

      $table->foreign('pos_account_id')
        ->references('id')
        ->on('pos_accounts')
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
    Schema::dropIfExists('lightspeed_retail_accounts');
  }
}
