<?php

namespace Tests\Unit\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BeaconAccountTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function test_a_beacon_account_belongs_to_a_location() {
		$location = factory(\App\Models\Business\Location::class)->create();
		$beaconAccount = factory(\App\Models\Business\BeaconAccount::class)->create(['location_id' => $location->id]);
		$this->assertInstanceOf('App\Models\Business\BeaconAccount', $location->beaconAccount);
	}

	public function test_a_location_has_one_beacon_account() {
		$location = factory(\App\Models\Business\Location::class)->create();
		$beaconAccount = factory(\App\Models\Business\BeaconAccount::class)->create(['location_id' => $location->id]);
		$this->assertInstanceOf('App\Models\Business\Location', $beaconAccount->location);
	}
}
