<?php

namespace Tests\Unit\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GeoAccountTest extends TestCase {
	use WithFaker, RefreshDatabase;

	public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

	public function test_a_geo_account_belongs_to_a_location() {
		$location = factory(\App\Models\Business\Location::class)->create();
		$geoAccount = factory(\App\Models\Business\GeoAccount::class)->create(['location_id' => $location->id]);
		$this->assertInstanceOf('App\Models\Business\GeoAccount', $location->geoAccount);
	}

	public function test_a_location_has_one_geo_account() {
		$location = factory(\App\Models\Business\Location::class)->create();
		$geoAccount = factory(\App\Models\Business\GeoAccount::class)->create(['location_id' => $location->id]);
		$this->assertInstanceOf('App\Models\Business\Location', $geoAccount->location);
	}

	public function test_creating_a_geo_account_sets_status_to_106() {
		$geoAccount = factory(\App\Models\Business\GeoAccount::class)->create();
		$this->assertEquals(106, $geoAccount->location->business->account->status->code);
	}
}
