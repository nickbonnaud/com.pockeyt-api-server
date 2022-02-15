<?php

namespace Tests\Unit\Customer;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ResetCodeTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_a_reset_code_belongs_to_a_customer() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\ResetCode::class)->create(['customer_id' => $customer->id]);
    $this->assertInstanceOf('App\Models\Customer\ResetCode', $customer->resetCode);
  }

  public function test_a_customer_has_one_reset_code() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $code = factory(\App\Models\Customer\ResetCode::class)->create(['customer_id' => $customer->id]);
    $this->assertInstanceOf('App\Models\Customer\Customer', $code->customer);
  }

  public function test_a_customer_can_create_a_reset_code() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $code = $customer->storeResetCode();
    $this->assertSame($code->value, $customer->resetCode->value);
  }
}
