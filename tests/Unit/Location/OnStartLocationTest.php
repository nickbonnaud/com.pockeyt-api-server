<?php

namespace Tests\Unit\Location;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OnStartLocationTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_on_start_location_belongs_to_a_customer() {
  	$customer = factory(\App\Models\Customer\Customer::class)->create();
  	$onStartLocation = factory(\App\Models\Location\OnStartLocation::class)->create(['customer_id' => $customer->id]);
  	$this->assertInstanceOf('App\Models\Location\OnStartLocation', $customer->onStartLocations->first());
  }

  public function test_a_customer_has_many_on_start_locations() {
  	$customer = factory(\App\Models\Customer\Customer::class)->create();
  	$onStartLocation = factory(\App\Models\Location\OnStartLocation::class, 3)->create(['customer_id' => $customer->id]);
  	$this->assertEquals(3, $customer->onStartLocations->count());
  }

  public function test_an_on_start_location_has_one_customer() {
  	$customer = factory(\App\Models\Customer\Customer::class)->create();
  	$onStartLocation = factory(\App\Models\Location\OnStartLocation::class)->create(['customer_id' => $customer->id]);
  	$this->assertInstanceOf('App\Models\Customer\Customer', $onStartLocation->customer);
  }

  public function test_an_on_start_location_belongs_to_a_region() {
  	$region = factory(\App\Models\Location\Region::class)->create();
  	$onStartLocation = factory(\App\Models\Location\OnStartLocation::class)->create(['region_id' => $region->id]);
  	$this->assertInstanceOf('App\Models\Location\OnStartLocation', $region->onStartLocations->first());
  }

  public function test_a_region_has_many_on_start_locations() {
  	$region = factory(\App\Models\Location\Region::class)->create();
  	$onStartLocation = factory(\App\Models\Location\OnStartLocation::class, 5)->create(['region_id' => $region->id]);
  	$this->assertEquals(5, $region->onStartLocations->count());
  }

  public function test_an_on_start_location_has_one_region() {
  	$region = factory(\App\Models\Location\Region::class)->create();
  	$onStartLocation = factory(\App\Models\Location\OnStartLocation::class)->create(['region_id' => $region->id]);
  	$this->assertInstanceOf('App\Models\Location\Region', $onStartLocation->region);
  }
}
