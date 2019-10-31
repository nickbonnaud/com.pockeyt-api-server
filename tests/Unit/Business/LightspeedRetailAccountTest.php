<?php

namespace Tests\Unit\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LightspeedRetailAccountTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function test_a_lightspeed_retail_account_creates_an_identifier() {
  	$posAccount = factory(\App\Models\Business\PosAccount::class)->create();
  	$lightSpeedAccount = factory(\App\Models\Business\LightspeedRetailAccount::class)->create(['pos_account_id' => $posAccount->id]);
  	$this->assertNotNull($lightSpeedAccount->identifier);
  }

  public function test_a_lightspeed_retail_account_belongs_to_a_pos_account() {
  	$posAccount = factory(\App\Models\Business\PosAccount::class)->create();
  	$lightSpeedAccount = factory(\App\Models\Business\LightspeedRetailAccount::class)->create(['pos_account_id' => $posAccount->id]);
  	$this->assertInstanceOf('App\Models\Business\LightspeedRetailAccount', $posAccount->lightspeedRetailAccount);
  }

  public function test_a_pos_account_has_one_lightspeed_retail_account() {
  	$posAccount = factory(\App\Models\Business\PosAccount::class)->create();
  	$lightSpeedAccount = factory(\App\Models\Business\LightspeedRetailAccount::class)->create(['pos_account_id' => $posAccount->id]);
  	$this->assertInstanceOf('App\Models\Business\PosAccount', $lightSpeedAccount->posAccount);
  }
}
