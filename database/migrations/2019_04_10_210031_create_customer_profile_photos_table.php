<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomerProfilePhotosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_profile_photos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('customer_profile_id');
            $table->unsignedInteger('avatar_id');
            $table->timestamps();

            $table->foreign('customer_profile_id')
                ->references('id')
                ->on('customer_profiles')
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
        Schema::dropIfExists('customer_profile_photos');
    }
}
