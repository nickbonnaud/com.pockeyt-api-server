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
    $business = factory(\App\Models\Business\Business::class)->create();

    $response = $this->json('GET', '/api/business/business')->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_authenticated_business_can_retrieve_their_own_data() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $headers = $this->businessHeaders($business);

    $response = $this->json('GET', '/api/business/business', $headers)->assertStatus(201);
    $response = $response->getData();

    $this->assertNotNull($response->data->email);
    $this->assertEquals($business->identifier, $response->data->identifier);
  }

  public function test_an_unauthorized_business_cannot_update_their_data() {
    $business = factory(\App\Models\Business\Business::class)->create();

    $attributes = [
        'email' => $this->faker->email
    ];

    $response = $this->json('PATCH', "/api/business/business/{$business->identifier}")->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_authorized_business_can_update_their_email() {
    $business = factory(\App\Models\Business\Business::class)->create();

    $attributes = [
        'email' => $this->faker->email
    ];

    $response = $this->json('PATCH', "/api/business/business/{$business->identifier}", $attributes, $this->businessHeaders($business))->getData();
    $this->assertEquals($attributes['email'], $response->data->email);
    $this->assertEquals($business->password, $business->fresh()->password);
  }

  public function test_an_authorized_business_can_update_their_password() {
    $oldPassword = $this->faker->password;
    $password = $this->faker->password;
    $business = factory(\App\Models\Business\Business::class)->create(['password' => $oldPassword]);

    $attributes = [
        'old_password' => $oldPassword,
        'password' => $password,
        'password_confirmation' => $password
    ];

    $this->assertTrue(Hash::check($oldPassword, $business->fresh()->password));
    $response = $this->json('PATCH', "/api/business/business/{$business->identifier}", $attributes, $this->businessHeaders($business))->getData();

    $this->assertEquals($business->email, $response->data->email);
    $this->assertTrue(Hash::check($password, $business->fresh()->password));
    $this->assertFalse(Hash::check($oldPassword, $business->fresh()->password));
  }

  public function test_an_authorized_business_must_include_password_confirm() {
    $oldPassword = $this->faker->password;
    $password = $this->faker->password;
    $business = factory(\App\Models\Business\Business::class)->create(['password' => $oldPassword]);

    $attributes = [
        'old_password' => $oldPassword,
        'password' => $password
    ];

    $response = $this->json('PATCH', "/api/business/business/{$business->identifier}", $attributes, $this->businessHeaders($business))->assertStatus(422);
    $response = $response->getData();

    $this->assertEquals('The given data was invalid.', $response->message);
    $this->assertEquals('The password confirmation does not match.', $response->errors->password[0]);
  }

  public function test_an_authorized_business_must_include_password() {
    $oldPassword = $this->faker->password;
    $password = $this->faker->password;
    $business = factory(\App\Models\Business\Business::class)->create(['password' => $oldPassword]);

    $attributes = [
        'old_password' => $oldPassword,
        'password_confirmation' => $password
    ];

    $response = $this->json('PATCH', "/api/business/business/{$business->identifier}", $attributes, $this->businessHeaders($business))->assertStatus(422);
    $response = $response->getData();
    $this->assertEquals('The given data was invalid.', $response->message);
    $this->assertEquals('The password field is required when email is not present.', $response->errors->password[0]);
  }

  public function test_an_authorized_business_must_include_old_password() {
    $oldPassword = $this->faker->password;
    $password = $this->faker->password;
    $business = factory(\App\Models\Business\Business::class)->create(['password' => $oldPassword]);

    $attributes = [
        'password' => $password,
        'password_confirmation' => $password
    ];

    $response = $this->json('PATCH', "/api/business/business/{$business->identifier}", $attributes, $this->businessHeaders($business))->assertStatus(422);
    $response = $response->getData();
    $this->assertEquals('The given data was invalid.', $response->message);
    $this->assertEquals('The old password field is required when email is not present.', $response->errors->old_password[0]);
  }

  public function test_an_authorized_business_old_password_must_match_password_stored() {
    $oldPassword = $this->faker->password;
    $password = $this->faker->password;
    $business = factory(\App\Models\Business\Business::class)->create(['password' => $oldPassword]);

    $attributes = [
        'old_password' => 'not_old_password',
        'password' => $password,
        'password_confirmation' => $password
    ];

    $response = $this->json('PATCH', "/api/business/business/{$business->identifier}", $attributes, $this->businessHeaders($business))->assertStatus(422);
    $response = $response->getData();
    $this->assertEquals('The given data was invalid.', $response->message);
    $this->assertEquals('Incorrect old password.', $response->errors->old_password[0]);
  }

  public function test_an_authorized_business_cannot_submit_empty_patch() {
    $business = factory(\App\Models\Business\Business::class)->create();

    $attributes = [];

    $response = $this->json('PATCH', "/api/business/business/{$business->identifier}", $attributes, $this->businessHeaders($business))->assertStatus(422);
    $response = $response->getData();
    $this->assertEquals('The given data was invalid.', $response->message);
    $this->assertEquals('The old password field is required when email is not present.', $response->errors->old_password[0]);
    $this->assertEquals('The password field is required when email is not present.', $response->errors->password[0]);
    $this->assertEquals('The email field is required when none of old password / password are present.', $response->errors->email[0]);
  }

  public function test_an_authorized_business_cannot_update_another_businesses_data() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $unauthorizedBusiness = factory(\App\Models\Business\Business::class)->create();

    $attributes = [
      'email' => $this->faker->email
    ];

    $response = $this->json('PATCH', "/api/business/business/{$business->identifier}", $attributes, $this->businessHeaders($unauthorizedBusiness))->assertStatus(403);
    $response = $response->getData();
    $this->assertEquals('Permission denied.', $response->errors);
  }
}