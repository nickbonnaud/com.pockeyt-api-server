<?php

namespace Tests\Unit\Customer;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Customer\CustomerStatus;
use Tests\TestCase;

class CustomerStatusTest extends TestCase {
	use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_a_customer_belongs_to_a_customer_status() {
  	$status = CustomerStatus::first();
  	$customer = factory(\App\Models\Customer\Customer::class)->create(['customer_status_id' => $status->id]);

  	$this->assertInstanceOf('App\Models\Customer\Customer', $status->customers->first());
  }

  public function test_a_customer_status_has_many_customers() {
  	$status = CustomerStatus::first();
  	$customer = factory(\App\Models\Customer\Customer::class, 3)->create(['customer_status_id' => $status->id]);
  	$this->assertEquals(3, $status->customers->count());
  }

  public function test_a_customer_has_one_status() {
  	$status = CustomerStatus::first();
  	$customer = factory(\App\Models\Customer\Customer::class)->create(['customer_status_id' => $status->id]);
  	$this->assertInstanceOf('App\Models\Customer\CustomerStatus', $customer->status);
  }
}
