<?php

namespace Tests\Feature\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LocationTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function test_creating_a_geo_account_creates_location() {
    factory(\App\Models\Business\AccountStatus::class)->create();
    $region = factory(\App\Models\Location\Region::class)->create();
    $geoAccount = factory(\App\Models\Business\GeoAccount::class)->create([
      'lat' => $region->center_lat,
      'lng' => $region->center_lng
    ]);
    $this->assertNotNull($geoAccount->location);
  }


  public function test_creating_location_result_in_geo_account_beacon_account_with_same_identifier() {
    factory(\App\Models\Business\AccountStatus::class)->create();
    $region = factory(\App\Models\Location\Region::class)->create();
    $geoAccount = factory(\App\Models\Business\GeoAccount::class)->create([
      'lat' => $region->center_lat,
      'lng' => $region->center_lng
    ]);
    $location = $geoAccount->location;
    $this->assertEquals($location->identifier, $geoAccount->identifier);
    $this->assertEquals($location->identifier, $location->beaconAccount->identifier);
  }
}
