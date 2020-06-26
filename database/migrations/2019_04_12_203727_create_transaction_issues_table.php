<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionIssuesTable extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::create('transaction_issues', function (Blueprint $table) {
      $table->id();
      $table->unsignedInteger('transaction_id');
      $table->uuid('identifier')->unique();
      $table->string('type');
      $table->text('issue');
      $table->boolean('resolved')->default(false);
      $table->unsignedInteger('prior_status_code');
      $table->timestamps();

      $table->foreign('transaction_id')
        ->references('id')
        ->on('transactions')
        ->onDelete('cascade');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {
    Schema::dropIfExists('transaction_issues');
  }
}
