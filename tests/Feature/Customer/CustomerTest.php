<?php

namespace Tests\Feature\Customer;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class CustomerTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function test_an_unauthorized_customer_cannot_retrieve_their_data() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();

    $response = $this->json('GET', '/api/customer/me')->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_authenticated_customer_can_retrieve_their_own_data() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $headers = $this->customerHeaders($customer);

    $response = $this->json('GET', '/api/customer/me', $headers)->assertStatus(201);
    $response = $response->getData();

    $this->assertNotNull($response->data->token);
    $this->assertNotEquals($headers['Authorization'], $response->data->token->value);
  }

  public function test_an_unauthorized_customer_cannot_update_their_data() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();

    $attributes = [
        'email' => $this->faker->email
    ];

    $response = $this->json('PATCH', "/api/customer/me/{$customer->identifier}")->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_authorized_customer_can_update_their_email() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();

    $attributes = [
        'email' => $this->faker->email
    ];

    $response = $this->json('PATCH', "/api/customer/me/{$customer->identifier}", $attributes, $this->customerHeaders($customer))->getData();
    $this->assertEquals($attributes['email'], $response->data->email);
    $this->assertEquals($customer->password, $customer->fresh()->password);
  }

  public function test_an_authorized_customer_can_update_their_password() {
    $oldPassword = $this->faker->password;
    $password = $this->faker->password;
    $customer = factory(\App\Models\Customer\Customer::class)->create(['password' => $oldPassword]);

    $attributes = [
        'old_password' => $oldPassword,
        'password' => $password,
        'password_confirmation' => $password
    ];

    $this->assertTrue(Hash::check($oldPassword, $customer->fresh()->password));
    $response = $this->json('PATCH', "/api/customer/me/{$customer->identifier}", $attributes, $this->customerHeaders($customer))->getData();

    $this->assertEquals($customer->email, $response->data->email);
    $this->assertTrue(Hash::check($password, $customer->fresh()->password));
    $this->assertFalse(Hash::check($oldPassword, $customer->fresh()->password));
  }

  public function test_an_authorized_customer_must_include_password_confirm() {
    $oldPassword = $this->faker->password;
    $password = $this->faker->password;
    $customer = factory(\App\Models\Customer\Customer::class)->create(['password' => $oldPassword]);

    $attributes = [
        'old_password' => $oldPassword,
        'password' => $password
    ];

    $response = $this->json('PATCH', "/api/customer/me/{$customer->identifier}", $attributes, $this->customerHeaders($customer))->assertStatus(422);
    $response = $response->getData();

    $this->assertEquals('The given data was invalid.', $response->message);
    $this->assertEquals('The password confirmation does not match.', $response->errors->password[0]);
  }

  public function test_an_authorized_customer_must_include_password() {
    $oldPassword = $this->faker->password;
    $password = $this->faker->password;
    $customer = factory(\App\Models\Customer\Customer::class)->create(['password' => $oldPassword]);

    $attributes = [
        'old_password' => $oldPassword,
        'password_confirmation' => $password
    ];

    $response = $this->json('PATCH', "/api/customer/me/{$customer->identifier}", $attributes, $this->customerHeaders($customer))->assertStatus(422);
    $response = $response->getData();
    $this->assertEquals('The given data was invalid.', $response->message);
    $this->assertEquals('The password field is required when email is not present.', $response->errors->password[0]);
  }

  public function test_an_authorized_customer_must_include_old_password() {
    $oldPassword = $this->faker->password;
    $password = $this->faker->password;
    $customer = factory(\App\Models\Customer\Customer::class)->create(['password' => $oldPassword]);

    $attributes = [
        'password' => $password,
        'password_confirmation' => $password
    ];

    $response = $this->json('PATCH', "/api/customer/me/{$customer->identifier}", $attributes, $this->customerHeaders($customer))->assertStatus(422);
    $response = $response->getData();
    $this->assertEquals('The given data was invalid.', $response->message);
    $this->assertEquals('The old password field is required when email is not present.', $response->errors->old_password[0]);
  }

  public function test_an_authorized_customer_old_password_must_match_password_stored() {
    $oldPassword = $this->faker->password;
    $password = $this->faker->password;
    $customer = factory(\App\Models\Customer\Customer::class)->create(['password' => $oldPassword]);

    $attributes = [
        'old_password' => 'not_old_password',
        'password' => $password,
        'password_confirmation' => $password
    ];

    $response = $this->json('PATCH', "/api/customer/me/{$customer->identifier}", $attributes, $this->customerHeaders($customer))->assertStatus(422);
    $response = $response->getData();
    $this->assertEquals('The given data was invalid.', $response->message);
    $this->assertEquals('Incorrect old password.', $response->errors->old_password[0]);
  }

  public function test_an_authorized_customer_cannot_submit_empty_patch() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();

    $attributes = [];

    $response = $this->json('PATCH', "/api/customer/me/{$customer->identifier}", $attributes, $this->customerHeaders($customer))->assertStatus(422);
    $response = $response->getData();
    $this->assertEquals('The given data was invalid.', $response->message);
    $this->assertEquals('The old password field is required when email is not present.', $response->errors->old_password[0]);
    $this->assertEquals('The password field is required when email is not present.', $response->errors->password[0]);
    $this->assertEquals('The email field is required when none of old password / password are present.', $response->errors->email[0]);
  }

  public function test_an_authorized_customer_cannot_update_another_customers_data() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $unauthorizedCustomer = factory(\App\Models\Customer\Customer::class)->create();

    $attributes = [
      'email' => $this->faker->email
    ];

    $response = $this->json('PATCH', "/api/customer/me/{$customer->identifier}", $attributes, $this->customerHeaders($unauthorizedCustomer))->assertStatus(403);
    $response = $response->getData();
    $this->assertEquals('Permission denied.', $response->errors);
  }
}
