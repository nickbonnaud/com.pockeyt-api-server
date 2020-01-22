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
		$statusId = \App\Models\Business\AccountStatus::where('code', 105)->first()->id;
		$account = factory(\App\Models\Business\Business::class)->create()->account;
		$account->account_status_id = $statusId;
		$account->save();
		$location = factory(\App\Models\Business\Location::class)->create(['business_id' => $account->business_id]);
		$geoAccount = factory(\App\Models\Business\GeoAccount::class)->create(['location_id' => $location->id]);
		$this->assertEquals(106, $geoAccount->fresh()->location->business->account->status->code);
	}
}
