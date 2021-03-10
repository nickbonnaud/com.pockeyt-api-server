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

  public function test_an_unauth_business_cannot_create_geo_account() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $lat = $this->faker->latitude;
    $lng = $this->faker->longitude;
    $radius = 50;
    factory(\App\Models\Location\Region::class)->create(['center_lat' => $lat, 'center_lng' => $lng]);
    $attributes = [
      'lat' => $lat,
      'lng' => $lng,
      'radius' => $radius
    ];

    $response = $this->send("", 'post', "/api/business/location/geo", $attributes)->assertUnauthorized();
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_business_can_create_geo_account() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $token = $this->createBusinessToken($business);
    $lat = $this->faker->latitude;
    $lng = $this->faker->longitude;
    $radius = 50;
    factory(\App\Models\Location\Region::class)->create(['center_lat' => $lat, 'center_lng' => $lng]);
    $attributes = [
      'lat' => $lat,
      'lng' => $lng,
      'radius' => $radius
    ];

    $response = $this->send($token, 'post', "/api/business/location/geo", $attributes)->getData();
    $this->assertDatabaseHas('geo_accounts', ['lat' => $lat, 'lng' => $lng, 'radius' => 50]);
    $this->assertEquals($response->data->lat, $lat);
    $this->assertEquals($response->data->lng, $lng);
    $this->assertEquals($response->data->radius, $radius);
  }

  public function test_creating_a_geo_account_creates_a_location() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $token = $this->createBusinessToken($business);
    $lat = $this->faker->latitude;
    $lng = $this->faker->longitude;
    $radius = 50;
    $region = factory(\App\Models\Location\Region::class)->create(['center_lat' => $lat, 'center_lng' => $lng]);
    $attributes = [
      'lat' => $lat,
      'lng' => $lng,
      'radius' => $radius
    ];

    $response = $this->send($token, 'post', "/api/business/location/geo", $attributes)->getData();
    $this->assertDatabaseHas('locations', ['business_id' => $business->id, 'region_id' => $region->id]);
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

    $response = $this->send("", 'patch', "/api/business/location/geo/{$geoAccount->identifier}", $attributes)->assertUnauthorized();
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_user_can_update_geo_account() {
    $geoAccount = factory(\App\Models\Business\GeoAccount::class)->create();
    $token = $this->createBusinessToken($geoAccount->location->business);
    $lat = $this->faker->latitude;
    $lng = $this->faker->longitude;
    $region = factory(\App\Models\Location\Region::class)->create(['center_lat' => $lat, 'center_lng' => $lng]);
    $radius = 75;
    $attributes = [
      'lat' => $lat,
      'lng' => $lng,
      'radius' => $radius
    ];

    $response = $this->send($token, 'patch', "/api/business/location/geo/{$geoAccount->identifier}", $attributes)->getData();

    $this->assertDatabaseHas('geo_accounts', ['lat' => $lat, 'lng' => $lng, 'radius' => $radius]);
    $this->assertEquals($lat, $response->data->lat);
    $this->assertEquals($lng, $response->data->lng);
    $this->assertEquals($radius, $response->data->radius);
  }

  public function test_an_auth_user_updating_geo_account_can_change_location_region() {
    $geoAccount = factory(\App\Models\Business\GeoAccount::class)->create();
    $oldRegionId = $geoAccount->location->region_id;
    $token = $this->createBusinessToken($geoAccount->location->business);
    $lat = $this->faker->latitude;
    $lng = $this->faker->longitude;
    $region = factory(\App\Models\Location\Region::class)->create(['center_lat' => $lat, 'center_lng' => $lng]);
    $radius = 75;
    $attributes = [
      'lat' => $lat,
      'lng' => $lng,
      'radius' => $radius
    ];

    $response = $this->send($token, 'patch', "/api/business/location/geo/{$geoAccount->identifier}", $attributes)->getData();

    $this->assertDatabaseHas('locations', ['business_id' => $geoAccount->location->business->id, 'region_id' => $region->id]);

    $this->assertDatabaseMissing('locations', ['business_id' => $geoAccount->location->business->id, 'region_id' => $oldRegionId]);
  }

  public function test_an_auth_user_can_only_update_their_geo_account() {
    $geoAccountOne = factory(\App\Models\Business\GeoAccount::class)->create();
    $geoAccountTwo = factory(\App\Models\Business\GeoAccount::class)->create();
    $token = $this->createBusinessToken($geoAccountOne->location->business);
    $lat = $this->faker->latitude;
    $lng = $this->faker->longitude;
    $attributes = [
      'lat' => $lat,
      'lng' => $lng,
      'radius' => 70
    ];

    $response = $this->send($token, 'patch', "/api/business/location/geo/{$geoAccountTwo->identifier}", $attributes)->assertStatus(403);

    $this->assertEquals('Permission denied.', ($response->getData())->errors);
  }
}
