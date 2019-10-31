<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProfilePhotosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profile_photos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('profile_id');
            $table->unsignedInteger('logo_id')->nullable();
            $table->unsignedInteger('banner_id')->nullable();
            $table->timestamps();

            $table->foreign('profile_id')
                ->references('id')
                ->on('profiles')
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
        Schema::dropIfExists('profile_photos');
    }
}
