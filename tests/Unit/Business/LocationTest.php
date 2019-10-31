<?php

namespace Tests\Unit\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LocationTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function test_a_location_creates_a_unique_identifier() {
    $location = factory(\App\Models\Business\Location::class)->create();
    $this->assertNotNull($location->identifier);
  }

  public function test_a_location_belongs_to_a_business() {
  	$business = factory(\App\Models\Business\Business::class)->create();
  	$location = factory(\App\Models\Business\Location::class)->create(['business_id' => $business->id]);
  	$this->assertInstanceOf('App\Models\Business\Location', $business->location);
  }

  public function test_a_business_has_one_location() {
  	$business = factory(\App\Models\Business\Business::class)->create();
  	$location = factory(\App\Models\Business\Location::class)->create(['business_id' => $business->id]);
  	$this->assertInstanceOf('App\Models\Business\Business', $location->business);
  }

  public function test_a_location_belongs_to_a_region() {
    $region = factory(\App\Models\Location\Region::class)->create();
    $location = factory(\App\Models\Business\Location::class)->create(['region_id' => $region->id]);
    $this->assertInstanceOf('App\Models\Business\Location', $region->locations->first());
  }

  public function test_a_region_can_have_many_locations() {
    $region = factory(\App\Models\Location\Region::class)->create();
    $location = factory(\App\Models\Business\Location::class, 3)->create(['region_id' => $region->id]);
    $this->assertEquals(3, $region->locations->count());
  }

  public function test_a_location_has_one_region() {
    $region = factory(\App\Models\Location\Region::class)->create();
    $location = factory(\App\Models\Business\Location::class)->create(['region_id' => $region->id]);
    $this->assertInstanceOf('App\Models\Location\Region', $location->region);
  }
}
