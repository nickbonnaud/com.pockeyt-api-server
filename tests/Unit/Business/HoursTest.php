<?php

namespace Tests\Unit\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HoursTest extends TestCase {
	use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_a_businesses_hours_creates_a_unique_identifier() {
  	$hours = factory(\App\Models\Business\Hours::class)->create();
  	$this->assertNotNull($hours->identifier);
  }

  public function test_business_hours_belongs_to_a_profile() {
  	$profile = factory(\App\Models\Business\Profile::class)->create();
  	$hours = factory(\App\Models\Business\Hours::class)->create(['profile_id' => $profile->id]);
  	$this->assertInstanceOf('App\Models\Business\Hours', $profile->hours);
  }

  public function test_a_profile_has_one_hours() {
  	$profile = factory(\App\Models\Business\Profile::class)->create();
  	$hours = factory(\App\Models\Business\Hours::class)->create(['profile_id' => $profile->id]);
  	$this->assertInstanceOf('App\Models\Business\Profile', $hours->profile);
  }
}
