<?php

namespace Tests\Unit\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PosAccountTest extends TestCase {
	use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

	public function test_a_pos_account_creates_an_identifier() {
    $account = factory(\App\Models\Business\PosAccount::class)->create();
    $this->assertNotNull($account->identifier);
  }

  public function test_a_pos_account_belongs_to_a_pos_account_status() {
		$status = \App\Models\Business\PosAccountStatus::first();
  	$account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id]);
  	$this->assertInstanceOf('App\Models\Business\PosAccount', $status->posAccounts->first());
	}

	public function test_a_pos_account_status_has_many_pos_accounts() {
  	$status = \App\Models\Business\PosAccountStatus::first();
  	$account = factory(\App\Models\Business\PosAccount::class, 5)->create(['pos_account_status_id' => $status->id]);
  	$this->assertEquals(5, $status->posAccounts->count());
  }

  public function test_a_pos_account_has_one_pos_status() {
  	$status = \App\Models\Business\PosAccountStatus::first();
  	$account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id]);
  	$this->assertInstanceOf('App\Models\Business\PosAccountStatus', $account->status);
  }

  public function test_a_pos_account_belongs_to_a_business() {
  	$business = factory(\App\Models\Business\Business::class)->create();
  	$account = factory(\App\Models\Business\PosAccount::class)->create(['business_id' => $business->id]);
  	$this->assertInstanceOf('App\Models\Business\PosAccount', $business->posAccount);
  }

  public function test_a_business_has_one_pos_account() {
    $business = factory(\App\Models\Business\Business::class)->create();
  	$account = factory(\App\Models\Business\PosAccount::class)->create(['business_id' => $business->id]);
  	$this->assertInstanceOf('App\Models\Business\Business', $account->business);
  }

  public function test_creating_a_pos_account_sets_account_status_to_120() {
    $account = factory(\App\Models\Business\PosAccount::class)->create();
    $this->assertEquals(120, $account->business->account->status->code);
  }
}
