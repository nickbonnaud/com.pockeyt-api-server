<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAchBusinessesTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('ach_businesses', function (Blueprint $table) {
      $table->increments('id');
      $table->uuid('identifier')->unique();
      $table->unsignedInteger('ach_account_id');
      $table->string('business_name');
      $table->string('address');
      $table->string('address_secondary')->nullable();
      $table->string('city');
      $table->string('state');
      $table->string('zip');
      $table->string('ein')->nullable();
      $table->string('business_url')->nullable();
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
    Schema::dropIfExists('ach_businesses');
  }
}
