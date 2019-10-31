<?php

namespace Tests\Unit\Customer;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function test_a_customer_creates_a_unique_identifier() {
  	$customer = factory(\App\Models\Customer\Customer::class)->create();
  	$this->assertNotNull($customer->identifier);
  }

  public function test_a_customer_password_is_not_changed_when_email_updated() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $password = $customer->password;

    $customer->update(['email' => $this->faker->email]);
    $this->assertEquals($password, $customer->password);
  }
}
