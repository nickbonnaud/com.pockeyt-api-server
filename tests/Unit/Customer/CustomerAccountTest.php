<?php

namespace Tests\Unit\Customer;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerAccountTest extends TestCase {
	use WithFaker, RefreshDatabase;

	public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

	public function test_a_customer_account_creates_an_identifier() {
		$account = factory(\App\Models\Customer\CustomerAccount::class)->create();
		$this->assertNotNull($account->identifier);
	}

	public function test_a_customer_account_belongs_to_a_customer() {
		$customer = factory(\App\Models\Customer\Customer::class)->create();
		$account = factory(\App\Models\Customer\CustomerAccount::class)->create(['customer_id' => $customer->id]);
		$this->assertInstanceOf('App\Models\Customer\CustomerAccount', $customer->account);
	}

	public function test_a_customer_has_one_account() {
		$customer = factory(\App\Models\Customer\Customer::class)->create();
		$account = factory(\App\Models\Customer\CustomerAccount::class)->create(['customer_id' => $customer->id]);
		$this->assertInstanceOf('App\Models\Customer\Customer', $account->customer);
	}
}
