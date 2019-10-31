<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInactiveItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inactive_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('active_id')->unique();
            $table->unsignedInteger('inventory_id');
            $table->string('main_id');
            $table->string('sub_id')->nullable();
            $table->string('name');
            $table->string('sub_name')->nullable();
            $table->string('category');
            $table->string('sub_category')->nullable();
            $table->integer('price');
            $table->timestamps();

            $table->foreign('inventory_id')
                ->references('id')
                ->on('inventories')
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
        Schema::dropIfExists('inactive_items');
    }
}
