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

  public function test_business_hours_are_automatically_json_encoded_on_save() {
    $rawHours = [
      'monday' => "Monday: 11:00 AM – 10:00 PM",
      'tuesday' => "Tuesday: 11:00 AM – 10:00 PM",
      'wednesday' => "Wednesday: 11:00 AM – 10:00 PM",
      'thursday' => "Thursday: 11:00 AM – 10:00 PM",
      'friday' => "Friday: 11:00 AM – 10:30 PM",
      'saturday' => "Saturday: 11:00 AM – 10:30 PM",
      'sunday' => "Sunday: 10:30 AM – 9:00 PM",
    ];
    $profile = factory(\App\Models\Business\Profile::class)->create([
      'hours' => $rawHours
    ]);
    $this->assertDatabaseHas('profiles', ['hours' => json_encode($rawHours)]);
  }

  public function test_business_hours_are_auto_json_decoded_on_retrieve() {
    $rawHours = [
      'monday' => "Monday: 11:00 AM – 10:00 PM",
      'tuesday' => "Tuesday: 11:00 AM – 10:00 PM",
      'wednesday' => "Wednesday: 11:00 AM – 10:00 PM",
      'thursday' => "Thursday: 11:00 AM – 10:00 PM",
      'friday' => "Friday: 11:00 AM – 10:30 PM",
      'saturday' => "Saturday: 11:00 AM – 10:30 PM",
      'sunday' => "Sunday: 10:30 AM – 9:00 PM",
    ];
    $profile = factory(\App\Models\Business\Profile::class)->create([
      'hours' => $rawHours
    ]);
    $this->assertSame('object', gettype($profile->hours));
    $this->assertSame('string', gettype($profile->getRawOriginal('hours')));
  }
}
