<?php

namespace Tests\Unit\Customer;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerProfileTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function test_a_customer_profile_creates_a_unique_identifier() {
  	$customerProfile = factory(\App\Models\Customer\CustomerProfile::class)->create();
  	$this->assertNotNull($customerProfile->identifier);
  }

  public function test_a_customer_profile_belongs_to_a_customer() {
  	$customer = factory(\App\Models\Customer\Customer::class)->create();
  	$customerProfile = factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
  	$this->assertInstanceOf('App\Models\Customer\CustomerProfile', $customer->profile);
  }

  public function test_a_customer_has_one_customer_profile() {
  	$customer = factory(\App\Models\Customer\Customer::class)->create();
  	$customerProfile = factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
  	$this->assertInstanceOf('App\Models\Customer\Customer', $customerProfile->customer);
  }
}
