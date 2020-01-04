<?php

namespace Tests\Feature\Customer;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Customer\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PasswordResetTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_customer_requesting_a_password_reset_requires_an_email_in_db() {
    $customer = factory(\App\Models\Customer\Customer::class)->create(['email' => 'test@pockeyt.com', 'password' => 'Password1!']);

    $body = ['email' => 'not_an email'];
    $response = $this->json('POST', '/api/customer/auth/request-reset', $body)->assertStatus(422);
    $this->assertEquals('The email must be a valid email address.', $response->getData()->errors->email[0]);

    $body = ['email' => 'fake@pockeyt.com'];
    $response = $this->json('POST', '/api/customer/auth/request-reset', $body)->assertStatus(422);
    $this->assertEquals('The selected email is invalid.', $response->getData()->errors->email[0]);
  }

  public function test_an_email_is_sent_to_customer_with_a_reset_password_token() {
    Notification::fake();

    $email = 'nick.bonnaud@gmail.com';
    $customer = factory(\App\Models\Customer\Customer::class)->create(['email' => $email, 'password' => 'Password1!']);

    $body = ['email' => $email];

    Notification::assertNothingSent();
    $response = $this->json('POST', '/api/customer/auth/request-reset', $body)->assertStatus(200);
    $response = $response->getData();

    Notification::assertSentTo(
      [$customer],
      ResetPassword::class,
      function ($notification, $channels, $notifiable) use ($customer) {
        return $notifiable->email === $customer->email &&
          !is_null($notification->token) &&
          $channels[0] === 'mail';
      }
    );

    $this->assertTrue($response->data->email_sent);
    $this->assertDatabaseHas('password_resets', ['email' => $customer->email]);
  }

  public function test_a_customer_resetting_a_password_requires_correct_data() {
    $email = 'fake.test@gmail.com';
    $password = 'Password1!';
    $customer = factory(\App\Models\Customer\Customer::class)->create(['email' => $email, 'password' => $password]);
    $token = app('auth.password.broker')->createToken($customer);

    DB::table('password_resets')->insert([
      'email' => $customer->email,
      'token' => $token
    ]);

    $body = [
      'password' => 'Password2@',
      'password_confirmation' => 'Password2@'
    ];

    $response = $this->json('PATCH', '/api/customer/auth/reset-password', $body)->assertStatus(422);
    $this->assertEquals('The token field is required.', $response->getData()->errors->token[0]);

    $body = [
      'password' => 'Password2@',
      'token' => $token
    ];

    $response = $this->json('PATCH', '/api/customer/auth/reset-password', $body)->assertStatus(422);
    $this->assertEquals('The password confirmation does not match.', $response->getData()->errors->password[0]);
  }

  public function test_a_customer_must_have_a_valid_token_when_resetting() {
    $email = 'fake.test@gmail.com';
    $password = 'Password1!';
    $customer = factory(\App\Models\Customer\Customer::class)->create(['email' => $email, 'password' => $password]);

    $token = app('auth.password.broker')->createToken($customer);

    DB::table('password_resets')->insert([
      'email' => $customer->email,
      'token' => $token
    ]);

    $body = [
      'password' => 'Password2@',
      'password_confirmation' => 'Password2@',
      'token' => "not_token"
    ];

    $response = $this->json('PATCH', '/api/customer/auth/reset-password', $body)->assertStatus(422);
    $this->assertEquals('The selected token is invalid.', $response->getData()->errors->token[0]);
  }

  public function test_a_customer_password_reset_token_is_valid_for_60_minutes() {
    $email = 'fake.test@gmail.com';
    $password = 'Password1!';
    $customer = factory(\App\Models\Customer\Customer::class)->create(['email' => $email, 'password' => $password]);

    $token = app('auth.password.broker')->createToken($customer);

    DB::table('password_resets')->insert([
      'email' => $customer->email,
      'token' => $token,
      'created_at' => Carbon::now()->subMinutes(70)
    ]);

    $newPassword = 'Password2@';
    $body = [
      'password' => $newPassword,
      'password_confirmation' => $newPassword,
      'token' => $token
    ];

    $response = $this->json('PATCH', '/api/customer/auth/reset-password', $body)->assertStatus(422);
    $this->assertEquals('Reset token has expired.', $response->getData()->errors->token[0]);
  }

  public function test_a_customer_can_reset_their_password() {
     $email = 'fake.test@gmail.com';
    $password = 'Password1!';
    $customer = factory(\App\Models\Customer\Customer::class)->create(['email' => $email, 'password' => $password]);

    $token = app('auth.password.broker')->createToken($customer);

    DB::table('password_resets')->insert([
      'email' => $customer->email,
      'token' => $token
    ]);

    $newPassword = 'Password2@';
    $body = [
      'password' => $newPassword,
      'password_confirmation' => $newPassword,
      'token' => $token
    ];

    $response = $this->json('PATCH', '/api/customer/auth/reset-password', $body)->assertStatus(200);
    $response = $response->getData();

    $this->assertTrue($response->data->reset);
    $this->assertEquals('passwords.reset', $response->data->res);
    $this->assertTrue(Hash::check($newPassword, $customer->fresh()->password));
  }
}
