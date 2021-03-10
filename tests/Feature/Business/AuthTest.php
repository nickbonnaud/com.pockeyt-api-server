<?php

namespace Tests\Feature\Business;

use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class AuthTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_a_business_can_register_and_is_returned_a_token() {
    $email = $this->faker->email;
    $password = $this->faker->password;
    $attributes = [
      'email' => $email,
      'password' => $password,
      'password_confirmation' => $password
    ];

    $response = $this->send("", 'post', '/api/business/auth/register', $attributes)->assertStatus(200);

    $this->assertSame('jwt', $response->headers->getCookies()[0]->getName());
    $this->assertNotNull($response->headers->getCookies()[0]->getValue());

    $response = $response->getData();
    $this->assertNotNull($response->data->csrf_token->value);
    $this->assertNotNull($response->data->business);
  }

  public function test_registering_a_new_business_requires_email_and_password() {
    $attributes = [];
    $response = $this->send("", 'post','/api/business/auth/register', $attributes)->getData();
    $this->assertNotNull($response->errors);
    $this->assertEquals('The email field is required.', $response->errors->email[0]);
    $this->assertEquals('The password field is required.', $response->errors->password[0]);
  }

  public function test_registering_a_new_business_requires_matching_passwords() {
    $attributes = [
      'email' => $this->faker->email,
      'password' => $this->faker->password,
      'password_confirmation' => 'password'
    ];

    $response = $this->send('', 'post', '/api/business/auth/register', $attributes)->getData();
    $this->assertNotNull($response->errors);
    $this->assertEquals('The password confirmation does not match.', $response->errors->password[0]);
  }

  public function test_registering_a_new_business_requires_password_min_of_six() {
    $email = $this->faker->email;
    $password = '12345';
    $attributes = [
      'email' => $email,
      'password' => $password,
      'password_confirmation' => $password
    ];

    $response = $this->send('', 'post', '/api/business/auth/register', $attributes)->getData();
    $this->assertNotNull($response->errors);
    $this->assertEquals('The password must be at least 6 characters.', $response->errors->password[0]);
  }

  public function test_registering_a_new_business_requires_proper_email() {
    $email = 'not_email';
    $password = $this->faker->password;
    $attributes = [
      'email' => $email,
      'password' => $password,
      'password_confirmation' => $password
    ];

    $response = $this->send('', 'post', '/api/business/auth/register', $attributes)->getData();
    $this->assertNotNull($response->errors);
    $this->assertEquals('The email must be a valid email address.', $response->errors->email[0]);
  }

  public function test_registering_a_new_business_requires_unique_email() {
    $email = $this->faker->email;
    factory(\App\Models\Business\Business::class)->create(['email' => $email]);

    $password = $this->faker->password;
    $attributes = [
      'email' => $email,
      'password' => $password,
      'password_confirmation' => $password
    ];

    $response = $this->send('', 'post', '/api/business/auth/register', $attributes)->getData();
    $this->assertNotNull($response->errors);
    $this->assertEquals('The email has already been taken.', $response->errors->email[0]);
  }

  public function test_a_business_with_correct_credentials_can_login() {
    $password = 'password1';
    $business = factory(\App\Models\Business\Business::class)->create(['password' => $password]);
    $attributes = [
        'email' => $business->email,
        'password' => $password
    ];

    $response = $this->send('', 'post', '/api/business/auth/login', $attributes)->assertStatus(200);
    $this->assertSame('jwt', $response->headers->getCookies()[0]->getName());
    $this->assertNotNull($response->headers->getCookies()[0]->getValue());

    $response = $response->getData();
    $this->assertNotNull($response->data->csrf_token->value);
    $this->assertNotNull($response->data->business);
  }

  public function test_a_business_with_incorrect_credentials_cannot_login() {
    $password = 'password1';
    $business = factory(\App\Models\Business\Business::class)->create(['password' => $password]);
    $attributes = [
        'email' => $business->email,
        'password' => 'not_password'
    ];

    $this->send('', 'post', '/api/business/auth/login', $attributes)->assertForbidden();

    $attributes = [
        'email' => 'wrong@gmail.com',
        'password' => $password
    ];

    $this->send('', 'post', '/api/business/auth/login', $attributes)->assertForbidden();
  }

  public function test_a_logged_in_business_can_logout() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $token = $this->createBusinessToken($business);
    $response = $this->send($token, 'get', '/api/business/auth/logout')->assertStatus(200);
    $response = $response->getData();
    $this->assertTrue($response->data->success);
    $this->assertNull(auth('business')->user());

    $response = $this->send($token, 'get', '/api/business/auth/logout')->assertStatus(401);
    $this->assertSame('Unauthenticated.', $response->getData()->message);
  }

  public function test_a_not_logged_in_business_cannot_logout() {
    factory(\App\Models\Business\Business::class)->create();
    $this->send('', 'get', '/api/business/auth/logout')->assertUnauthorized();
  }

  public function test_a_logged_in_business_can_refresh_their_token() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $token = $this->createBusinessToken($business);
    $csrfToken = auth('business')->payload()->get('csrf-token');
    $response = $this->send($token, 'get', '/api/business/auth/refresh')->assertStatus(200);

    $this->assertNotSame($csrfToken, $response->getData()->data->csrf_token->value);
    $this->assertNotSame($token, $response->headers->getCookies()[0]->getValue());
    $this->assertNotSame($response->getData()->data->csrf_token->value, $response->headers->getCookies()[0]->getValue());


    $headers = [
      'Accept' => 'application/json',
      'csrf-token' => $response->getData()->data->csrf_token->value
    ];
    $newResponse = $this
      ->withCookie('jwt', $response->headers->getCookies()[0]->getValue())
      ->withHeaders($headers)
      ->get('/api/business/auth/logout', [])->getData();

    $this->assertTrue($newResponse->data->success);
  }

  public function test_a_not_logged_in_business_cannot_refresh_their_token() {
    factory(\App\Models\Business\Business::class)->create();

    $response = $this->send('', 'get', '/api/business/auth/logout')->assertUnauthorized();
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_unauth_business_cannot_verify_correct_current_password() {
    $password = 'p@ssw0rd!';
    factory(\App\Models\Business\Business::class)->create(['password' => $password]);

    $formData = [
      'password' => $password
    ];

    $response = $this->send('', 'post', '/api/business/auth/verify', $formData)->assertUnauthorized();
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_business_can_verify_correct_current_password() {
    $password = 'p@ssw0rd!';
    $business = factory(\App\Models\Business\Business::class)->create(['password' => $password]);
    $token = $this->createBusinessToken($business);

    $formData = [
      'password' => $password
    ];

    $response = $this->send($token, 'post', '/api/business/auth/verify', $formData)->assertStatus(200);
    $this->assertTrue($response->getData()->data->password_verified);
  }

  public function test_verify_password_returns_false_if_not_correct_password() {
    $password = 'p@ssw0rd!';
    $business = factory(\App\Models\Business\Business::class)->create(['password' => $password]);
    $token = $this->createBusinessToken($business);

    $formData = [
      'password' => ''
    ];

    $response = $this->send($token, 'post', '/api/business/auth/verify', $formData)->assertStatus(401);
    $this->assertFalse($response->getData()->data->password_verified);
  }
}
