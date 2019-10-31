<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAchOwnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ach_owners', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('identifier')->unique();
            $table->unsignedInteger('ach_account_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('title');
            $table->string('email');
            $table->string('type')->default('business');
            $table->date('dob');
            $table->string('address');
            $table->string('address_secondary')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('zip');
            $table->string('country')->default('US');
            $table->binary('ssn');
            $table->boolean('primary')->default(true);
            $table->string('owner_url')->nullable();
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
        Schema::dropIfExists('ach_owners');
    }
}
