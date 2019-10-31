<?php

namespace Tests\Unit\Business;

use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase {
	use WithFaker, RefreshDatabase;

	public function test_a_business_password_is_auto_hashed_when_created() {
  	$password = 'password';
  	$business = factory(\App\Models\Business\Business::class)->create(['password' => $password]);
  	$this->assertNotEquals($password, $business->password);
  	$this->assertTrue(Hash::check($password, $business->password));
  }

  public function test_a_business_password_is_auto_hashed_when_password_updated() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $oldPassword = $business->password;
    $newPassword = 'password_new';
    $business->update(['password' => $newPassword]);

    $this->assertNotEquals($newPassword, $business->password);
    $this->assertTrue(Hash::check($newPassword, $business->password));
    $this->assertFalse(Hash::check($oldPassword, $business->password));
  }
}
