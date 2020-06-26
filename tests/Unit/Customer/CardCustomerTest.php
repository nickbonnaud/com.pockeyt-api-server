<?php

namespace Tests\Unit\Customer;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CardCustomerTest extends TestCase {
	use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

	public function test_a_card_customer_creates_a_shopper_reference() {
		$cardCustomer = factory(\App\Models\Customer\CardCustomer::class)->create();
		$this->assertNotNull($cardCustomer->shopper_reference);
	}

	public function test_a_card_customer_belongs_to_a_customer_account() {
  	$account = factory(\App\Models\Customer\CustomerAccount::class)->create();
  	$cardCustomer = factory(\App\Models\Customer\CardCustomer::class)->create(['customer_account_id' => $account->id]);
  	$this->assertInstanceOf('App\Models\Customer\CardCustomer', $account->card);
  }

  public function test_a_customer_account_has_one_card_customer() {
  	$account = factory(\App\Models\Customer\CustomerAccount::class)->create();
  	$cardCustomer = factory(\App\Models\Customer\CardCustomer::class)->create(['customer_account_id' => $account->id]);
  	$this->assertInstanceOf('App\Models\Customer\CustomerAccount', $cardCustomer->account);
  }

  public function test_creating_card_customer_sets_account_primary_to_card() {
  	$account = factory(\App\Models\Customer\CustomerAccount::class)->create(['primary' => 'ach']);
  	$cardCustomer = factory(\App\Models\Customer\CardCustomer::class)->create(['customer_account_id' => $account->id]);
  	$this->assertEquals('card', $account->fresh()->primary);
  }

  public function test_creating_card_customer_sets_customer_status_to_correct_value() {
    $account = factory(\App\Models\Customer\CustomerAccount::class)->create(['primary' => 'ach']);
    $customer = $account->customer;
    $customer->customer_status_id = \App\Models\Customer\CustomerStatus::where('code', 103)->first()->id;
    $customer->save();
    $cardCustomer = factory(\App\Models\Customer\CardCustomer::class)->create(['customer_account_id' => $account->id]);
    $this->assertEquals(120, $customer->fresh()->status->code);
  }

  public function test_creating_card_customer_does_not_change_status_if_already_has_payment_source() {
    $account = factory(\App\Models\Customer\CustomerAccount::class)->create(['primary' => 'ach']);
    $customer = $account->customer;
    $customer->customer_status_id = \App\Models\Customer\CustomerStatus::where('code', 200)->first()->id;
    $customer->save();
    $cardCustomer = factory(\App\Models\Customer\CardCustomer::class)->create(['customer_account_id' => $account->id]);
    $this->assertEquals(200, $customer->fresh()->status->code);
  }
}
