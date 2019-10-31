<?php

namespace Tests\Feature\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BeaconAccountTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function test_creating_geo_account_creates_beacon_account() {
    $location = factory(\App\Models\Business\Location::class)->create();
    $profile = factory(\App\Models\Business\Profile::class)->create(['business_id' => $location->business->id]);
    $geoAccount = factory(\App\Models\Business\GeoAccount::class)->create(['location_id' => $location->id]);
    $this->assertDatabaseHas('beacon_accounts', ['identifier' => $geoAccount->identifier]);
  }
}
