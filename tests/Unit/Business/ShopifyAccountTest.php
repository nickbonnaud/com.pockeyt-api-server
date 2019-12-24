<?php

namespace Tests\Unit\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShopifyAccountTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_a_shopify_account_creates_an_identifier() {
  	$posAccount = factory(\App\Models\Business\PosAccount::class)->create();
  	$shopifyAccount = factory(\App\Models\Business\ShopifyAccount::class)->create(['pos_account_id' => $posAccount->id]);
  	$this->assertNotNull($shopifyAccount->identifier);
  }

  public function test_a_shopify_account_belongs_to_a_pos_account() {
  	$posAccount = factory(\App\Models\Business\PosAccount::class)->create();
  	$shopifyAccount = factory(\App\Models\Business\ShopifyAccount::class)->create(['pos_account_id' => $posAccount->id]);
  	$this->assertInstanceOf('App\Models\Business\ShopifyAccount', $posAccount->shopifyAccount);
  }

  public function test_a_pos_account_has_one_shopify_account() {
  	$posAccount = factory(\App\Models\Business\PosAccount::class)->create();
  	$shopifyAccount = factory(\App\Models\Business\ShopifyAccount::class)->create(['pos_account_id' => $posAccount->id]);
  	$this->assertInstanceOf('App\Models\Business\PosAccount', $shopifyAccount->posAccount);
  }
}
