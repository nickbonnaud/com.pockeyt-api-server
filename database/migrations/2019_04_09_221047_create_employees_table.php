<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeesTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('employees', function (Blueprint $table) {
      $table->increments('id');
      $table->uuid('identifier')->unique();
      $table->unsignedInteger('business_id');
      $table->string('external_id');
      $table->string('first_name');
      $table->string('last_name');
      $table->string('email')->nullable();
      $table->timestamps();

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
    Schema::dropIfExists('employees');
  }
}
