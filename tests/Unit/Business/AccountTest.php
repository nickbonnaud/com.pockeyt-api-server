<?php

namespace Tests\Unit\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccountTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_account_belongs_to_an_account_status() {
  	$status = \App\Models\Business\AccountStatus::first();
  	$account = factory(\App\Models\Business\Account::class)->create(['account_status_id' => $status->id]);
  	$this->assertInstanceOf('App\Models\Business\Account', $status->accounts->first());
  }

  public function test_an_account_status_has_many_accounts() {
  	$business = factory(\App\Models\Business\Business::class)->create();
    factory(\App\Models\Business\Business::class)->create();
    $status = $business->account->status;
  	$this->assertEquals(2, $status->accounts->count());
  }

  public function test_an_account_has_one_status() {
  	$status = factory(\App\Models\Business\AccountStatus::class)->create();
  	$account = factory(\App\Models\Business\Account::class)->create(['account_status_id' => $status->id]);
  	$this->assertInstanceOf('App\Models\Business\AccountStatus', $account->status);
  }

  public function test_an_account_belongs_to_a_business() {
    factory(\App\Models\Business\AccountStatus::class)->create();
  	$business = factory(\App\Models\Business\Business::class)->create();
  	$account = factory(\App\Models\Business\Account::class)->create(['business_id' => $business->id]);
  	$this->assertInstanceOf('App\Models\Business\Account', $business->account);
  }

  public function test_a_business_has_one_account() {
  	factory(\App\Models\Business\AccountStatus::class)->create();
    $business = factory(\App\Models\Business\Business::class)->create();
  	$account = factory(\App\Models\Business\Account::class)->create(['business_id' => $business->id]);
  	$this->assertInstanceOf('App\Models\Business\Business', $account->business);
  }

  public function test_an_account_is_assigned_a_status_when_created() {
    $account = factory(\App\Models\Business\Account::class)->create();
    $this->assertNotNull($account->account_status_id);
  }
}
