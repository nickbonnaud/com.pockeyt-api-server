<?php

namespace Tests\Unit\Customer;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PushTokenTest extends TestCase {
	use WithFaker, RefreshDatabase;

	public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

	public function test_a_push_token_belongs_to_a_customer() {
		$customer = factory(\App\Models\Customer\Customer::class)->create();
		$pushToken = factory(\App\Models\Customer\PushToken::class)->create(['customer_id' => $customer->id]);
		$this->assertInstanceOf('App\Models\Customer\PushToken', $customer->pushToken);
	}

	public function test_a_customer_has_one_push_token() {
		$customer = factory(\App\Models\Customer\Customer::class)->create();
		$pushToken = factory(\App\Models\Customer\PushToken::class)->create(['customer_id' => $customer->id]);
		$this->assertInstanceOf('App\Models\Customer\Customer', $pushToken->customer);
	}
}
