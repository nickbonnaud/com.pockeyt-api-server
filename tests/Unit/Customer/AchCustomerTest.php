<?php

namespace Tests\Unit\Customer;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AchCustomerTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_ach_customer_belongs_to_a_customer_account() {
  	$account = factory(\App\Models\Customer\CustomerAccount::class)->create();
  	$achAccount = factory(\App\Models\Customer\AchCustomer::class)->create(['customer_account_id' => $account->id]);
  	$this->assertInstanceOf('App\Models\Customer\AchCustomer', $account->ach);
  }

  public function test_a_customer_account_has_one_ach_customer() {
  	$account = factory(\App\Models\Customer\CustomerAccount::class)->create();
  	$achAccount = factory(\App\Models\Customer\AchCustomer::class)->create(['customer_account_id' => $account->id]);
  	$this->assertInstanceOf('App\Models\Customer\CustomerAccount', $achAccount->account);
  }

  public function test_creating_ach_customer_sets_account_primary_to_ach() {
  	$account = factory(\App\Models\Customer\CustomerAccount::class)->create(['primary' => 'card']);
  	$achAccount = factory(\App\Models\Customer\AchCustomer::class)->create(['customer_account_id' => $account->id]);
  	$this->assertEquals('ach', $account->fresh()->primary);
  }

  public function test_creating_ach_customer_sets_customer_status_to_correct_value() {
    $account = factory(\App\Models\Customer\CustomerAccount::class)->create(['primary' => 'card']);
    $customer = $account->customer;
    $customer->customer_status_id = \App\Models\Customer\CustomerStatus::where('code', 103)->first()->id;
    $customer->save();
    $achAccount = factory(\App\Models\Customer\AchCustomer::class)->create(['customer_account_id' => $account->id]);
    $this->assertEquals(120, $customer->fresh()->status->code);
  }

  public function test_creating_ach_customer_does_not_change_status_if_already_has_payment_source() {
    $account = factory(\App\Models\Customer\CustomerAccount::class)->create(['primary' => 'card']);
    $customer = $account->customer;
    $customer->customer_status_id = \App\Models\Customer\CustomerStatus::where('code', 200)->first()->id;
    $customer->save();
    $achAccount = factory(\App\Models\Customer\AchCustomer::class)->create(['customer_account_id' => $account->id]);
    $this->assertEquals(200, $customer->fresh()->status->code);
  }
}
