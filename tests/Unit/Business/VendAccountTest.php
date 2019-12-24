<?php

namespace Tests\Unit\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VendAccountTest extends TestCase {
	use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }
  
  public function test_a_vend_account_creates_an_identifier() {
  	$posAccount = factory(\App\Models\Business\PosAccount::class)->create();
  	$vendAccount = factory(\App\Models\Business\VendAccount::class)->create(['pos_account_id' => $posAccount->id]);
  	$this->assertNotNull($vendAccount->identifier);
  }

  public function test_a_vend_account_belongs_to_a_pos_account() {
  	$posAccount = factory(\App\Models\Business\PosAccount::class)->create();
  	$vendAccount = factory(\App\Models\Business\VendAccount::class)->create(['pos_account_id' => $posAccount->id]);
  	$this->assertInstanceOf('App\Models\Business\VendAccount', $posAccount->vendAccount);
  }

  public function test_a_pos_account_has_one_vend_account() {
  	$posAccount = factory(\App\Models\Business\PosAccount::class)->create();
  	$vendAccount = factory(\App\Models\Business\VendAccount::class)->create(['pos_account_id' => $posAccount->id]);
  	$this->assertInstanceOf('App\Models\Business\PosAccount', $vendAccount->posAccount);
  }
}
