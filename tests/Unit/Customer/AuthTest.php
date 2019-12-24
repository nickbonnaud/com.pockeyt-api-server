<?php

namespace Tests\Unit\Customer;

use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase {
	use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

	public function test_a_customer_password_is_auto_hashed_when_created() {
  	$password = 'password';
  	$customer = factory(\App\Models\Customer\Customer::class)->create(['password' => $password]);
  	$this->assertNotEquals($password, $customer->password);
  	$this->assertTrue(Hash::check($password, $customer->password));
  }

  public function test_a_customer_password_is_auto_hashed_when_password_updated() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $oldPassword = $customer->password;
    $newPassword = 'password_new';
    $customer->update(['password' => $newPassword]);

    $this->assertNotEquals($newPassword, $customer->password);
    $this->assertTrue(Hash::check($newPassword, $customer->password));
    $this->assertFalse(Hash::check($oldPassword, $customer->password));
  }
}
