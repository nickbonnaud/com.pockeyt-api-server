<?php

namespace Tests\Feature\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;

class BusinessTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unauthorized_business_cannot_retrieve_their_data() {
    factory(\App\Models\Business\Business::class)->create();

    $response = $this->send('', 'get', '/api/business/business')->assertUnauthorized();
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_authenticated_business_can_retrieve_their_own_data() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $token = $this->createBusinessToken($business);

    $response = $this->send($token, 'get', '/api/business/business')->assertStatus(201);
    $response = $response->getData();

    $this->assertNotNull($response->data->email);
    $this->assertEquals($business->identifier, $response->data->identifier);
  }

  public function test_an_unauthorized_business_cannot_update_their_data() {
    $business = factory(\App\Models\Business\Business::class)->create();

    $attributes = [
      'email' => $this->faker->email
    ];

    $response = $this->send('', 'patch', "/api/business/business/{$business->identifier}", $attributes)->assertUnauthorized();
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_authorized_business_can_update_their_email() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $token = $this->createBusinessToken($business);

    $attributes = [
      'email' => $this->faker->email
    ];

    $response = $this->send($token,'patch', "/api/business/business/{$business->identifier}", $attributes)->getData();
    $this->assertEquals($attributes['email'], $response->data->email);
    $this->assertEquals($business->password, $business->fresh()->password);
  }

  public function test_an_authorized_business_can_update_their_password() {
    $password = $this->faker->password;
    $business = factory(\App\Models\Business\Business::class)->create();
    $token = $this->createBusinessToken($business);

    $attributes = [
      'password' => $password,
      'password_confirmation' => $password
    ];

    $response = $this->send($token, 'patch', "/api/business/business/{$business->identifier}", $attributes)->getData();
    $this->assertEquals($business->email, $response->data->email);
    $this->assertTrue(Hash::check($password, $business->fresh()->password));
  }

  public function test_an_authorized_business_must_include_confirm() {
    $password = $this->faker->password;
    $business = factory(\App\Models\Business\Business::class)->create();
    $token = $this->createBusinessToken($business);

    $attributes = [
      'password' => $password
    ];

    $response = $this->send($token, 'patch', "/api/business/business/{$business->identifier}", $attributes)->assertStatus(422);
    $response = $response->getData();

    $this->assertEquals('The given data was invalid.', $response->message);
    $this->assertEquals('The password confirmation does not match.', $response->errors->password[0]);
  }

  public function test_an_authorized_business_must_include_password() {
    $password = $this->faker->password;
    $business = factory(\App\Models\Business\Business::class)->create();
    $token = $this->createBusinessToken($business);

    $attributes = [
      'password_confirmation' => $password
    ];

    $response = $this->send($token, 'patch', "/api/business/business/{$business->identifier}", $attributes)->assertStatus(422);
    $response = $response->getData();
    $this->assertEquals('The given data was invalid.', $response->message);
    $this->assertEquals('The password field is required when email is not present.', $response->errors->password[0]);
  }

  public function test_an_authorized_business_cannot_submit_empty_patch() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $token = $this->createBusinessToken($business);

    $attributes = [];

    $response = $this->send($token, 'patch', "/api/business/business/{$business->identifier}", $attributes)->assertStatus(422);
    $response = $response->getData();
    $this->assertEquals('The given data was invalid.', $response->message);
    $this->assertEquals('The password field is required when email is not present.', $response->errors->password[0]);
    $this->assertEquals('The email field is required when none of password are present.', $response->errors->email[0]);
  }

  public function test_an_authorized_business_cannot_update_another_businesses_data() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $unauthorizedBusiness = factory(\App\Models\Business\Business::class)->create();
    $token = $this->createBusinessToken($unauthorizedBusiness);

    $attributes = [
      'email' => $this->faker->email
    ];

    $response = $this->send($token, 'patch', "/api/business/business/{$business->identifier}", $attributes)->assertStatus(403);
    $response = $response->getData();
    $this->assertEquals('Permission denied.', $response->errors);
  }

  // public function test_example_business_test_data() {
  //   $business = factory(\App\Models\Business\Business::class)->create();
  //   $businessId = $business->id;

  //   $profileId = factory(\App\Models\Business\Profile::class)->create(['business_id' => $businessId])->id;
  //   $payFacAccountId = factory(\App\Models\Business\PayFacAccount::class)->create(['account_id' => $business->account->id])->id;
  //   factory(\App\Models\Business\PayFacBusiness::class)->create(['pay_fac_account_id' => $payFacAccountId]);
  //   factory(\App\Models\Business\PayFacOwner::class, 2)->create(['pay_fac_account_id' => $payFacAccountId, 'percent_ownership' => 25]);
  //   factory(\App\Models\Business\PayFacBank::class)->create(['pay_fac_account_id' => $payFacAccountId]);
  //   $region = factory(\App\Models\Location\Region::class)->create();
  //   $locationId = factory(\App\Models\Business\Location::class)->create(['business_id' => $businessId])->id;
  //   factory(\App\Models\Business\GeoAccount::class)->create([
  //     'location_id' => $locationId,
  //     'lat' => $region->center_lat,
  //     'lng' => $region->center_lng
  //   ]);
  //   factory(\App\Models\Business\PosAccount::class)->create(['business_id' => $businessId]);
  //   factory(\App\Models\Business\Hours::class)->create(['profile_id' => $profileId]);

  //   $token = $this->createBusinessToken($business);
  //   $response = $this->send($token, 'get', '/api/business/auth/refresh')->getData();
  //   dd($response);
  // }
}