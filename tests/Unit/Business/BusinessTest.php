<?php

namespace Tests\Unit\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BusinessTest extends TestCase {
	use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }
	
  public function test_a_business_creates_a_unique_identifier() {
  	$business = factory(\App\Models\Business\Business::class)->create();
  	$this->assertNotNull($business->identifier);
  }

  public function test_a_business_password_is_not_changed_when_email_updated() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $password = $business->password;

    $business->update(['email' => $this->faker->email]);
    $this->assertEquals($password, $business->password);
  }

  public function test_a_business_owns_a_profile() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $profile = factory(\App\Models\Business\Profile::class)->make();
    $business->profile()->save($profile);
    $this->assertInstanceOf('App\Models\Business\Profile', $business->profile);
  }
}
