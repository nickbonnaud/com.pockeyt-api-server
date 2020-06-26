<?php

namespace Tests\Feature\Location;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OnStartLocationTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unauth_customer_cannot_create_on_start_location() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $lat = $this->faker->latitude;
    $lng = $this->faker->longitude;
    $region = factory(\App\Models\Location\Region::class)->create(['center_lat' => $lat, 'center_lng' => $lng]);
    $locations = factory(\App\Models\Business\Location::class, 9)->create(['region_id' => $region->id]);

    $body = [
      'lat' => $lat,
      'lng' => $lng,
      'start_location' => true
    ];

    $response = $this->json('POST', 'api/customer/geo-location', $body)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_cutomer_can_create_an_on_start_location_no_region() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $lat = $this->faker->latitude;
    $lng = $this->faker->longitude;
    $region = factory(\App\Models\Location\Region::class)->create(['center_lat' => $this->faker->latitude, 'center_lng' => $this->faker->longitude]);
    $locations = factory(\App\Models\Business\Location::class, 9)->create(['region_id' => $region->id]);
    $headers = $this->customerHeaders($customer);

    $body = [
      'lat' => $lat,
      'lng' => $lng,
      'start_location' => true
    ];

    $response = $this->json('POST', 'api/customer/geo-location', $body, $headers)->getData();
    $this->assertDatabaseHas('on_start_locations', ['customer_id' => $customer->id, 'lat' => $lat, 'lng' => $lng, 'region_id' => null]);
    $this->assertEquals(0, count($response->data));
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
      'start_location' => true
    ];

    $response = $this->json('POST', 'api/customer/geo-location', $attributes, $headers)->getData();
    $this->assertDatabaseHas('on_start_locations', ['customer_id' => $customer->id, 'lat' => $lat, 'lng' => $lng, 'region_id' => $inRegion->id]);
    $this->assertEquals(0, count($response->data));
  }

  public function test_a_customer_can_create_on_start_and_is_returned_all_locations_in_region() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $lat = $this->faker->latitude;
    $lng = $this->faker->longitude;
    $region = factory(\App\Models\Location\Region::class)->create(['center_lat' => $lat, 'center_lng' => $lng]);
    $locations = factory(\App\Models\Business\Location::class, 9)->create(['region_id' => $region->id]);
    $headers = $this->customerHeaders($customer);

    $body = [
      'lat' => $lat,
      'lng' => $lng,
      'start_location' => true
    ];

    $response = $this->json('POST', 'api/customer/geo-location', $body, $headers)->getData();
    $this->assertDatabaseHas('on_start_locations', ['customer_id' => $customer->id, 'lat' => $lat, 'lng' => $lng, 'region_id' => $region->id]);
    $this->assertEquals(9, count($response->data));
  }
}
