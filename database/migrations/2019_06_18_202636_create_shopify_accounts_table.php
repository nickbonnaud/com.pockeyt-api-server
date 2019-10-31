<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopifyAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('shopify_accounts', function (Blueprint $table) {
        $table->increments('id');
        $table->uuid('identifier')->unique();
        $table->unsignedInteger('pos_account_id');
        $table->string('access_token');
        $table->string('shop_id');
        $table->boolean('webhook_set')->default(false);
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
        Schema::dropIfExists('shopify_accounts');
    }
}
