<?php

namespace Tests\Feature\Customer;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerProfileTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unauth_customer_cannot_create_a_profile() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();

    $attributes = [
      'first_name' => $this->faker->firstName,
      'last_name' => $this->faker->lastName
    ];

    $response = $this->json('POST', '/api/customer/profile', $attributes)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_customer_can_create_a_profile() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $headers = $this->customerHeaders($customer);
    $firstName = $this->faker->firstName;
    $lastName = $this->faker->lastName;

    $attributes = [
      'first_name' => $firstName,
      'last_name' => $lastName
    ];

    $response = $this->json('POST', '/api/customer/profile', $attributes, $headers)->getData();
    $this->assertEquals($firstName, $response->data->profile->first_name);
    $this->assertEquals($lastName, $response->data->profile->last_name);
    $this->assertEquals(101, $response->data->status->code);
    $this->assertDatabaseHas('customer_profiles', ['identifier' => $response->data->profile->identifier]);
  }

  public function test_creating_a_profile_sets_correct_customer_status() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $headers = $this->customerHeaders($customer);
    $firstName = $this->faker->firstName;
    $lastName = $this->faker->lastName;

    $attributes = [
      'first_name' => $firstName,
      'last_name' => $lastName
    ];

    $response = $this->json('POST', '/api/customer/profile', $attributes, $headers)->getData();
    $this->assertEquals(101, $customer->fresh()->status->code);
  }

  public function test_an_auth_customer_must_submit_correct_data() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $headers = $this->customerHeaders($customer);

    $attributes = [
      'first_name' => 'a'
    ];

    $response = $this->json('POST', '/api/customer/profile', $attributes, $headers)->assertStatus(422);
    $response = $response->getData();
    $this->assertEquals('The given data was invalid.', $response->message);
    $this->assertEquals('The first name must be at least 2 characters.', $response->errors->first_name[0]);
    $this->assertEquals('The last name field is required.', $response->errors->last_name[0]);
  }

  public function test_unauth_customer_cannot_get_profile_data() {
    $profile = factory(\App\Models\Customer\CustomerProfile::class)->create();
    $response = $this->json('GET', '/api/customer/profile')->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_auth_customer_can_get_profile_data() {
    $profile = factory(\App\Models\Customer\CustomerProfile::class)->create();
    $headers = $this->customerHeaders($profile->customer);
    $response = $this->json('GET', '/api/customer/profile', $headers)->getData();
    $this->assertEquals($profile->last_name, $response->data->last_name);
  }

  public function test_an_unauth_customer_cannot_update_profile() {
    $profile = factory(\App\Models\Customer\CustomerProfile::class)->create();
    $firstName = $this->faker->firstName;
    $lastName = $this->faker->lastName;

    $attributes = [
      'first_name' => $firstName,
      'last_name' => $lastName
    ];

    $response = $this->json('PATCH', "/api/customer/profile/{$profile->identifier}", $attributes)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_customer_can_update_profile() {
    $profile = factory(\App\Models\Customer\CustomerProfile::class)->create();
    $headers = $this->customerHeaders($profile->customer);
    $firstName = $this->faker->firstName;
    $lastName = $this->faker->lastName;

    $attributes = [
      'first_name' => $firstName,
      'last_name' => $lastName
    ];

    $response = $this->json('PATCH', "/api/customer/profile/{$profile->identifier}", $attributes, $headers)->getData();
    $this->assertEquals($firstName, $response->data->profile->first_name);
    $this->assertEquals($lastName, $response->data->profile->last_name);
    $this->assertDatabaseHas('customer_profiles', ['last_name' => $lastName]);
  }

  public function test_an_auth_customer_cannot_update_another_profile() {
    $profile = factory(\App\Models\Customer\CustomerProfile::class)->create();
    $unauthorizedProfile = factory(\App\Models\Customer\CustomerProfile::class)->create();
    $headers = $this->customerHeaders($unauthorizedProfile->customer);
    $firstName = $this->faker->firstName;
    $lastName = $this->faker->lastName;

    $attributes = [
      'first_name' => $firstName,
      'last_name' => $lastName
    ];

    $response = $this->json('PATCH', "/api/customer/profile/{$profile->identifier}", $attributes, $headers)->assertStatus(403);
    $response = $response->getData();
    $this->assertEquals('Permission denied.', $response->errors);
  }
}
