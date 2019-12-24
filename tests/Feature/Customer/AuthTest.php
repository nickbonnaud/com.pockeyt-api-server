<?php

namespace Tests\Feature\Customer;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_a_customer_can_register_and_is_returned_a_token() {
    $email = $this->faker->email;
    $password = $this->faker->password;
    $attributes = [
      'email' => $email,
      'password' => $password,
      'password_confirmation' => $password
    ];

    $response = $this->json('POST', '/api/customer/auth/register', $attributes)->getData();
    $this->assertDatabaseHas('customers', ['email' => $email]);
    $this->assertNotEmpty($response->data->token->value);
    $this->assertNull($response->errors->email[0]);
  }

  public function test_registering_a_new_customer_requires_email_and_password() {
    $attributes = [];
    $response = $this->json('POST', '/api/customer/auth/register', $attributes)->getData();
    $this->assertNotNull($response->errors);
    $this->assertEquals('The email field is required.', $response->errors->email[0]);
    $this->assertEquals('The password field is required.', $response->errors->password[0]);
  }

  public function test_registering_a_new_customer_requires_matching_passwords() {
    $attributes = [
      'email' => $this->faker->email,
      'password' => $this->faker->password,
      'password_confirmation' => 'password'
    ];
    $response = $this->json('POST', '/api/customer/auth/register', $attributes)->getData();
    $this->assertNotNull($response->errors);
    $this->assertEquals('The password confirmation does not match.', $response->errors->password[0]);
  }

  public function test_registering_a_new_customer_requires_password_min_of_six() {
    $email = $this->faker->email;
    $password = '12345';
    $attributes = [
      'email' => $email,
      'password' => $password,
      'password_confirmation' => $password
    ];

    $response = $this->json('POST', '/api/customer/auth/register', $attributes)->getData();
    $this->assertNotNull($response->errors);
    $this->assertEquals('The password must be at least 6 characters.', $response->errors->password[0]);
  }

  public function test_registering_a_new_customer_requires_proper_email() {
    $email = 'not_email';
    $password = $this->faker->password;
    $attributes = [
      'email' => $email,
      'password' => $password,
      'password_confirmation' => $password
    ];

    $response = $this->json('POST', '/api/customer/auth/register', $attributes)->getData();
    $this->assertNotNull($response->errors);
    $this->assertEquals('The email must be a valid email address.', $response->errors->email[0]);
  }

  public function test_registering_a_new_customer_requires_unique_email() {
    $email = $this->faker->email;
    factory(\App\Models\Customer\Customer::class)->create(['email' => $email]);

    $password = $this->faker->password;
    $attributes = [
      'email' => $email,
      'password' => $password,
      'password_confirmation' => $password
    ];

    $response = $this->json('POST', '/api/customer/auth/register', $attributes)->getData();
    $this->assertNotNull($response->errors);
    $this->assertEquals('The email has already been taken.', $response->errors->email[0]);
  }

  public function test_a_customer_with_correct_credentials_can_login() {
    $password = 'password1';
    $customer = factory(\App\Models\Customer\Customer::class)->create(['password' => $password]);
    $attributes = [
        'email' => $customer->email,
        'password' => $password
    ];

    $response = $this->json('POST', '/api/customer/auth/login', $attributes)->getData();
    $this->assertNotEmpty($response->data->token->value);
    $this->assertNull($response->errors->email[0]);
  }

  public function test_a_customer_with_incorrect_credentials_cannot_login() {
    $password = 'password1';
    $customer = factory(\App\Models\Customer\Customer::class)->create(['password' => $password]);
    $attributes = [
        'email' => $customer->email,
        'password' => 'not_password'
    ];

    $response = $this->json('POST', '/api/customer/auth/login', $attributes)->getData();
    $this->assertNotNull($response->errors->email[0]);
    $this->assertEquals('invalid_credentials', $response->errors->email[0]);

    $attributes = [
        'email' => 'wrong@gmail.com',
        'password' => $password
    ];

    $response = $this->json('POST', '/api/customer/auth/login', $attributes)->getData();
    $this->assertNotNull($response->errors->email[0]);
    $this->assertEquals('invalid_credentials', $response->errors->email[0]);
  }

  public function test_a_logged_in_customer_can_logout() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $headers = $this->customerHeaders($customer);

    $response = $this->json('GET', '/api/customer/auth/logout', $headers)->assertStatus(200);
    $response = $response->getData();
    $this->assertNull($response->data->token->value);

    $response = $this->json('GET', '/api/customer/auth/logout', $headers)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_a_not_logged_in_customer_cannot_logout() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();

    $response = $this->json('GET', '/api/customer/auth/logout')->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_a_logged_in_customer_can_refresh_their_token() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $headers = $this->customerHeaders($customer);

    $response = $this->json('GET', '/api/customer/auth/refresh', $headers)->assertStatus(200);
    $response = $response->getData();
    $this->assertNotNull($response->data->token->value);
    $this->assertNotEquals($headers['Authorization'], $response->data->token->value);

    $response = $this->json('GET', '/api/customer/auth/refresh', $headers)->assertStatus(500);
    $this->assertEquals('The token has been blacklisted', ($response->getData())->message);
  }

  public function test_a_not_logged_in_customer_cannot_refresh_their_token() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();

    $response = $this->json('GET', '/api/customer/auth/logout')->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }
}
