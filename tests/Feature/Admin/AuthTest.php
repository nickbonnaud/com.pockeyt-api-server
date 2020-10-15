<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Admin;

class AuthTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_registering_an_admin_requires_proper_data() {
    $email = $this->faker->email;
    $password = 'P@ssword1';
    $roleCode = Admin\Role::first()->code;

    $formData = [
      'email' => 'not an_email',
      'password' => '12a!',
      'password_confirmation' => '12a$',
      'role_code' => '34c*'
    ];

    $response = $this->json('POST', '/api/admin/auth/register', $formData)->assertStatus(422);
    $this->assertSame('The given data was invalid.', ($response->getData())->message);
    $this->assertSame('The email must be a valid email address.', $response->getData()->errors->email[0]);
    $this->assertSame('The password must be at least 8 characters.', $response->getData()->errors->password[0]);
    $this->assertSame('The password confirmation does not match.', $response->getData()->errors->password[1]);
    $this->assertSame('The password format is invalid.', $response->getData()->errors->password[2]);
    $this->assertSame('The role code must be a number.', $response->getData()->errors->role_code[0]);
  }

  public function test_an_admin_can_register_and_is_returned_a_token() {
    $email = $this->faker->email;
    $password = 'P@ssword1';
    $role = Admin\Role::where('name', 'help')->first();

    $formData = [
      'email' => $email,
      'password' => $password,
      'password_confirmation' => $password,
      'role_code' => $role->code
    ];

    $response = $this->json('POST', '/api/admin/auth/register', $formData)->getData();

    $this->assertDatabaseHas('admins', [
      'email' => $email,
      'role_id' => $role->id,
      'approved' => false
    ]);

    $this->assertNotEmpty($response->data->token);
    $this->assertSame($role->name, $response->data->role->name);
  }

  public function test_an_admin_with_invalid_credentials_cannot_login() {
    $password = 'P@ssword1';
    $admin = factory(\App\Models\Admin\Admin::class)->create(['password' => $password]);

    $formData = [
      'email' => 'wrong email.com',
      'password' => '123'
    ];

    $response = $this->json('POST', '/api/admin/auth/login', $formData)->assertStatus(422);
    $this->assertSame('The given data was invalid.', ($response->getData())->message);
    $this->assertSame('The email must be a valid email address.', $response->getData()->errors->email[0]);
    $this->assertSame('The password must be at least 8 characters.', $response->getData()->errors->password[0]);
    $this->assertSame('The password format is invalid.', $response->getData()->errors->password[1]);
  }

  public function test_an_admin_cannot_login_with_incorrect_credentials() {
    $password = 'P@ssword1';
    $admin = factory(\App\Models\Admin\Admin::class)->create(['password' => $password]);

    $formData = [
      'email' => 'wrong@email.com',
      'password' => $password . 'd4$'
    ];

    $response = $this->json('POST', '/api/admin/auth/login', $formData)->assertStatus(401);
    $this->assertSame('The given data was invalid.', ($response->getData())->message);
    $this->assertSame('Incorred email or password.', $response->getData()->errors->login[0]);
  }

  public function test_an_admin_can_login_with_correct_credentials() {
    $password = 'Password1!';
    $admin = factory(\App\Models\Admin\Admin::class)->create(['password' => $password]);

    $formData = [
      'email' => $admin->email,
      'password' => $password
    ];

    $response = $this->json('POST', '/api/admin/auth/login', $formData)->getData();
    $this->assertNotEmpty($response->data->token);
    $this->assertSame($admin->role->name, $response->data->role->name);
  }

  public function test_an_unauthenticated_admin_cannot_logout() {
    $admin = factory(\App\Models\Admin\Admin::class)->create();
    $response = $this->json('GET', '/api/admin/auth/logout')->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_authenticated_admin_can_logout() {
    $admin = factory(\App\Models\Admin\Admin::class)->create();
    $headers = $this->adminHeaders($admin);

    $response = $this->json('GET', '/api/admin/auth/logout', $headers)->assertStatus(200);
    $response = $response->getData();
    $this->assertSame('Success.', $response->message);

    $response = $this->json('GET', '/api/admin/auth/logout', $headers)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_unauth_admin_cannot_refresh_their_token() {
    $admin = factory(\App\Models\Admin\Admin::class)->create();
    $response = $this->json('GET', '/api/admin/auth/refresh')->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_admin_can_refresh_their_token() {
    $admin = factory(\App\Models\Admin\Admin::class)->create();
    $headers = $this->adminHeaders($admin);

    $response = $this->json('GET', '/api/admin/auth/refresh')->assertStatus(201);
    $this->assertNotSame($headers['Authorization'], $response->getData()->data->token);

    $response = $this->json('GET', '/api/admin/auth/refresh')->assertStatus(500);
    $this->assertEquals('The token has been blacklisted', ($response->getData())->message);
  }
}
