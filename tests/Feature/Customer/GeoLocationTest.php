<?php

namespace Tests\Feature\Customer;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class GeoLocationTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unauth_customer_cannot_fetch_geo_locations() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $lat = $this->faker->latitude;
    $lng = $this->faker->longitude;
    $region = factory(\App\Models\Location\Region::class)->create(['center_lat' => $lat, 'center_lng' => $lng]);
    $locations = factory(\App\Models\Business\Location::class, 9)->create(['region_id' => $region->id]);

    $body = [
      'lat' => $lat,
      'lng' => $lng,
      'start_location' => false
    ];

    $response = $this->json('POST', 'api/customer/geo-location', $body)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_customer_can_fetch_geo_locations() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $lat = $this->faker->latitude;
    $lng = $this->faker->longitude;
    $region = factory(\App\Models\Location\Region::class)->create(['center_lat' => $lat, 'center_lng' => $lng]);
    $locations = factory(\App\Models\Business\Location::class, 9)->create(['region_id' => $region->id]);
    $headers = $this->customerHeaders($customer);

    $body = [
      'lat' => $lat,
      'lng' => $lng,
      'start_location' => false
    ];

    $response = $this->json('POST', 'api/customer/geo-location', $body, $headers)->getData();
    $this->assertEquals(9, count($response->data));
  }

  public function test_an_auth_customer_is_returned_no_geo_locations_if_not_in_region() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $lat = $this->faker->latitude;
    $lng = $this->faker->longitude;
    $region = factory(\App\Models\Location\Region::class)->create(['center_lat' => $this->faker->latitude, 'center_lng' => $this->faker->longitude]);
    $locations = factory(\App\Models\Business\Location::class, 9)->create(['region_id' => $region->id]);
    $headers = $this->customerHeaders($customer);

    $body = [
      'lat' => $lat,
      'lng' => $lng,
      'start_location' => false
    ];

    $response = $this->json('POST', 'api/customer/geo-location', $body, $headers)->getData();
    $this->assertEquals(0, count($response->data));
  }
}
