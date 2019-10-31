<?php

namespace Tests\Feature\Location;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OnStartLocationTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function test_an_unauth_customer_cannot_create_on_start_location() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $region = factory(\App\Models\Location\Region::class, 20)->create();

    $attributes = [
      'lat' => $this->faker->latitude,
      'lng' => $this->faker->longitude,
      'beacon_start' => false
    ];

    $response = $this->json('POST', "/api/customer/start", $attributes)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_cutomer_can_create_an_on_start_location_no_region_no_location() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $regions = factory(\App\Models\Location\Region::class, 20)->create();
    $headers = $this->customerHeaders($customer);

    $lat = $this->faker->latitude;
    $lng = $this->faker->longitude;
    $attributes = [
      'lat' => $lat,
      'lng' => $lng,
      'beacon_start' => false
    ];

    $response = $this->json('POST', "/api/customer/start", $attributes, $headers)->assertStatus(200);
    $this->assertDatabaseHas('on_start_locations', ['customer_id' => $customer->id, 'lat' => $lat, 'lng' => $lng, 'region_id' => null, 'location_id' => null]);
    $this->assertEquals(0, count($response->getData()->data));
  }

  public function test_an_auth_cutomer_can_create_an_on_start_location_no_location() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();

    $lat = $this->faker->latitude;
    $lng = $this->faker->longitude;
    $inRegion = factory(\App\Models\Location\Region::class)->create(['center_lat' => $lat, 'center_lng' => $lng]);

    $regions = factory(\App\Models\Location\Region::class, 19)->create();
    $headers = $this->customerHeaders($customer);

    $attributes = [
      'lat' => $lat,
      'lng' => $lng,
      'beacon_start' => false
    ];

    $response = $this->json('POST', "/api/customer/start", $attributes, $headers)->assertStatus(200);
    $this->assertDatabaseHas('on_start_locations', ['customer_id' => $customer->id, 'lat' => $lat, 'lng' => $lng, 'region_id' => $inRegion->id, 'location_id' => null]);
  }

  public function test_a_customer_can_create_on_start_and_is_returned_all_locations_in_region() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();

    $lat = $this->faker->latitude;
    $lng = $this->faker->longitude;
    $inRegion = factory(\App\Models\Location\Region::class)->create(['center_lat' => $lat, 'center_lng' => $lng]);
    $inLocation = factory(\App\Models\Business\Location::class)->create(['region_id' => $inRegion->id]);
    factory(\App\Models\Business\GeoAccount::class)->create(['location_id' => $inLocation->id]);

    $otherLocationsInRegion = factory(\App\Models\Business\Location::class, 9)->create(['region_id' => $inRegion->id]);

    $locationsNotInRegion = factory(\App\Models\Business\Location::class, 5)->create();

    $regions = factory(\App\Models\Location\Region::class, 19)->create();
    $headers = $this->customerHeaders($customer);

    $attributes = [
      'lat' => $lat,
      'lng' => $lng,
      'location_identifier' => $inLocation->identifier,
      'beacon_start' => true
    ];

    $response = $this->json('POST', "/api/customer/start", $attributes, $headers)->getData();
    $this->assertEquals(10, count($response->data));
    $this->assertNotNull($response->data[0]->geo_coords);
    $this->assertNotNull($response->data[0]->beacon);
    $this->assertDatabaseHas('on_start_locations', ['customer_id' => $customer->id, 'lat' => $lat, 'lng' => $lng, 'region_id' => $inRegion->id, 'location_id' => $inLocation->id]);
  }
}
