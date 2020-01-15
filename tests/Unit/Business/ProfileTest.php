<?php

namespace Tests\Unit\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProfileTest extends TestCase {
	use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_a_business_profile_creates_a_unique_identifier() {
  	$businessProfile = factory(\App\Models\Business\Profile::class)->create();
  	$this->assertNotNull($businessProfile->identifier);
  }

  public function test_a_business_profile_is_owned_by_a_profile() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $profile = factory(\App\Models\Business\Profile::class)->make();
    $business->profile()->save($profile);
    $this->assertInstanceOf('App\Models\Business\Business', $profile->business);
  }

  public function test_creating_a_profile_auto_creates_profile_photos() {
    $profile = factory(\App\Models\Business\Profile::class)->create();
    $this->assertInstanceOf('App\Models\Business\ProfilePhotos', $profile->photos);
  }

  public function test_creating_a_profile_changes_the_account_status_to_101() {
    $profile = factory(\App\Models\Business\Profile::class)->create();
    $this->assertEquals(101, $profile->business->account->fresh()->status->code);
  }
}
