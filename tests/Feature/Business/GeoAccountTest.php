<?php

namespace Tests\Feature\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GeoAccountTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_auth_business_can_create_geo_account() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $header = $this->businessHeaders($business);
    $lat = $this->faker->latitude;
    $lng = $this->faker->longitude;
    $radius = 50;
    factory(\App\Models\Location\Region::class)->create(['center_lat' => $lat, 'center_lng' => $lng]);
    $attributes = [
      'lat' => $lat,
      'lng' => $lng,
      'radius' => $radius
    ];
    $response = $this->json('POST', "/api/business/location/geo", $attributes, $header)->getData();
    $this->assertDatabaseHas('geo_accounts', ['lat' => $lat, 'lng' => $lng, 'radius' => 50]);
    $this->assertEquals($response->data->lat, $lat);
    $this->assertEquals($response->data->lng, $lng);
    $this->assertEquals($response->data->radius, $radius);
  }

  public function test_creating_a_geo_account_creates_a_location() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $header = $this->businessHeaders($business);
    $lat = $this->faker->latitude;
    $lng = $this->faker->longitude;
    $radius = 50;
    $region = factory(\App\Models\Location\Region::class)->create(['center_lat' => $lat, 'center_lng' => $lng]);
    $attributes = [
      'lat' => $lat,
      'lng' => $lng,
      'radius' => $radius
    ];
    $response = $this->json('POST', "/api/business/location/geo", $attributes, $header)->getData();
    $this->assertDatabaseHas('locations', ['business_id' => $business->id, 'region_id' => $region->id]);
  }

  public function test_an_unauth_user_cannot_retrieve_geo_account() {
    factory(\App\Models\Business\GeoAccount::class)->create();
    $response = $this->json('GET', '/api/business/location/geo')->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_user_can_retrieve_geo_account() {
    $geoAccount = factory(\App\Models\Business\GeoAccount::class)->create();
    $header = $this->businessHeaders($geoAccount->location->business);
    $response = $this->json('GET', '/api/business/location/geo', $header)->getData();
    $this->assertEquals($geoAccount->lat, $response->data->lat);
    $this->assertEquals($geoAccount->lng, $response->data->lng);
  }

  public function test_an_unauth_user_cannot_update_geo_account() {
    $geoAccount = factory(\App\Models\Business\GeoAccount::class)->create();
    $lat = $this->faker->latitude;
    $lng = $this->faker->longitude;
    $attributes = [
      'lat' => $lat,
      'lng' => $lng,
      'radius' => 100
    ];
    $response = $this->json('PATCH', "/api/business/location/geo/{$geoAccount->identifier}", $attributes)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_user_can_update_geo_account() {
    $geoAccount = factory(\App\Models\Business\GeoAccount::class)->create();
    $header = $this->businessHeaders($geoAccount->location->business);
    $lat = $this->faker->latitude;
    $lng = $this->faker->longitude;
    $region = factory(\App\Models\Location\Region::class)->create(['center_lat' => $lat, 'center_lng' => $lng]);
    $radius = 75;
    $attributes = [
      'lat' => $lat,
      'lng' => $lng,
      'radius' => $radius
    ];
    $response = $this->json('PATCH', "/api/business/location/geo/{$geoAccount->identifier}", $attributes, $header)->getData();
    $this->assertDatabaseHas('geo_accounts', ['lat' => $lat, 'lng' => $lng, 'radius' => $radius]);
    $this->assertEquals($lat, $response->data->lat);
    $this->assertEquals($lng, $response->data->lng);
    $this->assertEquals($radius, $response->data->radius);
  }

  public function test_an_auth_user_updating_geo_account_can_change_location_region() {
    $geoAccount = factory(\App\Models\Business\GeoAccount::class)->create();
    $oldRegionId = $geoAccount->location->region_id;
    $header = $this->businessHeaders($geoAccount->location->business);
    $lat = $this->faker->latitude;
    $lng = $this->faker->longitude;
    $region = factory(\App\Models\Location\Region::class)->create(['center_lat' => $lat, 'center_lng' => $lng]);
    $radius = 75;
    $attributes = [
      'lat' => $lat,
      'lng' => $lng,
      'radius' => $radius
    ];
    $response = $this->json('PATCH', "/api/business/location/geo/{$geoAccount->identifier}", $attributes, $header)->getData();
    $this->assertDatabaseHas('locations', ['business_id' => $geoAccount->location->business->id, 'region_id' => $region->id]);

    $this->assertDatabaseMissing('locations', ['business_id' => $geoAccount->location->business->id, 'region_id' => $oldRegionId]);
  }

  public function test_an_auth_user_can_only_update_their_geo_account() {
    $geoAccountOne = factory(\App\Models\Business\GeoAccount::class)->create();
    $geoAccountTwo = factory(\App\Models\Business\GeoAccount::class)->create();
    $header = $this->businessHeaders($geoAccountOne->location->business);
    $lat = $this->faker->latitude;
    $lng = $this->faker->longitude;
    $attributes = [
      'lat' => $lat,
      'lng' => $lng,
      'radius' => 70
    ];
    $response = $this->json('PATCH', "/api/business/location/geo/{$geoAccountTwo->identifier}", $attributes, $header)->assertStatus(403);
    $this->assertEquals('Permission denied.', ($response->getData())->errors);
  }
}
