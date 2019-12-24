<?php

namespace Tests\Unit\Customer;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProfilePhotoTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_a_customer_profile_photo_belongs_to_a_profile() {
  	$profile = factory(\App\Models\Customer\CustomerProfile::class)->create();
  	$photo = factory(\App\Models\Customer\CustomerProfilePhoto::class)->make();
  	$profile->photo()->save($photo);
  	$this->assertInstanceOf('App\Models\Customer\CustomerProfilePhoto', $profile->photo);
  }

  public function test_profile_has_one_customer_profile_photo() {
  	$profile = factory(\App\Models\Customer\CustomerProfile::class)->create();
  	$photo = factory(\App\Models\Customer\CustomerProfilePhoto::class)->make();
  	$profile->photo()->save($photo);
  	$this->assertInstanceOf('App\Models\Customer\CustomerProfile', $photo->profile);
  }

  public function test_a_customer_profile_photo_belongs_to_an_avatar() {
  	$photo = factory(\App\Models\Customer\CustomerPhoto::class)->create();
  	$profilePhoto = factory(\App\Models\Customer\CustomerProfilePhoto::class)->create(['avatar_id' => $photo->id]);
  	$this->assertInstanceOf('App\Models\Customer\CustomerProfilePhoto', $photo->avatar);
  }

  public function test_an_avatar_has_one_customer_profile_photo() {
  	$photo = factory(\App\Models\Customer\CustomerPhoto::class)->create();
  	$profilePhoto = factory(\App\Models\Customer\CustomerProfilePhoto::class)->create(['avatar_id' => $photo->id]);
  	$this->assertInstanceOf('App\Models\Customer\CustomerPhoto', $profilePhoto->avatar);
  }
}
