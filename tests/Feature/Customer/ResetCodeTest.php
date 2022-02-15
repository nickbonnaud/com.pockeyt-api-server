<?php

namespace Tests\Feature\Customer;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Customer\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;


class ResetCodeTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_a_customer_can_request_a_password_reset() {
    Notification::fake();
    $customer = factory(\App\Models\Customer\Customer::class)->create();

    $attributes = [
      'email' => $customer->email
    ];

    $response = $this->json('POST', 'api/customer/auth/request-reset', $attributes)->getData();
    $this->assertTrue($response->data->email_sent);
    $this->assertDatabaseHas("reset_codes", ['customer_id' => $customer->id]);
    Notification::assertSentTo(
      [$customer],
      PasswordReset::class
    );
  }

  public function test_a_customer_email_must_exist_to_request_a_password_reset() {
    $attributes = [
      'email' => $this->faker->email
    ];

    $response = $this->json('POST', 'api/customer/auth/request-reset', $attributes)->assertStatus(422);
    $this->assertEquals('The selected email is invalid.', $response->getData()->errors->email[0]);
  }

  public function test_a_customer_can_reset_their_password() {
    $resetCode = factory(\App\Models\Customer\ResetCode::class)->create();
    $customer = $resetCode->customer;

    $this->assertDatabaseHas("reset_codes", ['id' => $resetCode->id]);

    $attributes = [
      'email' => $customer->email,
      'reset_code' => $resetCode->value,
      'password' => "hd4@3GS!gS*G2",
      'password_confirmation' => "hd4@3GS!gS*G2"
    ];

    $response = $this->json('PATCH', 'api/customer/auth/reset-password', $attributes)->getData();

    $this->assertTrue($response->data->password_reset);
    $this->assertDatabaseMissing("reset_codes", ['id' => $resetCode->id]);

    $this->assertNull($customer->fresh()->resetCode);
    $this->assertTrue(Hash::check("hd4@3GS!gS*G2", $customer->fresh()->password));
  }

  public function test_a_customer_must_supply_correct_attributes() {
    $resetCode = factory(\App\Models\Customer\ResetCode::class)->create();
    $customer = $resetCode->customer;

    $this->assertDatabaseHas("reset_codes", ['id' => $resetCode->id]);

    $attributes = [
      'email' => $this->faker->freeEmail,
      'reset_code' => "123456",
      'password' => "hd4=",
      'password_confirmation' => "gS*G2"
    ];

    $response = $this->json('PATCH', 'api/customer/auth/reset-password', $attributes)->assertStatus(422);
    $response = $response->getData();

    $this->assertSame("The password confirmation does not match.", $response->errors->password[0]);
    $this->assertSame("The password must be at least 8 characters.", $response->errors->password[1]);

    $this->assertSame("The selected email is invalid.", $response->errors->email[0]);

    $this->assertSame("The selected reset code is invalid.", $response->errors->reset_code[0]);
    $this->assertSame("Invalid Reset Code", $response->errors->reset_code[1]);
  }

  public function test_a_customer_must_supply_their_own_reset_code() {
    $resetCode = factory(\App\Models\Customer\ResetCode::class)->create();
    $customer = $resetCode->customer;

    $this->assertDatabaseHas("reset_codes", ['id' => $resetCode->id]);

    $attributes = [
      'email' => $customer->email,
      'reset_code' => factory(\App\Models\Customer\ResetCode::class)->create()->value,
      'password' => "hd4@3GS!gS*G2",
      'password_confirmation' => "hd4@3GS!gS*G2"
    ];

    $response = $this->json('PATCH', 'api/customer/auth/reset-password', $attributes)->assertStatus(422);
    $response = $response->getData();

    $this->assertSame("Invalid Reset Code", $response->errors->reset_code[0]);
  }

  public function test_a_customer_must_supply_reset_code_within_ten_minutes() {
    $resetCode = factory(\App\Models\Customer\ResetCode::class)->create(["created_at" => Carbon::now()->subMinutes(11)]);
    $customer = $resetCode->customer;

    $this->assertDatabaseHas("reset_codes", ['id' => $resetCode->id]);

    $attributes = [
      'email' => $customer->email,
      'reset_code' => $resetCode->value,
      'password' => "hd4@3GS!gS*G2",
      'password_confirmation' => "hd4@3GS!gS*G2"
    ];

    $response = $this->json('PATCH', 'api/customer/auth/reset-password', $attributes)->assertStatus(422);
    $response = $response->getData();

    $this->assertSame("Reset token has expired.", $response->errors->reset_code[0]);
  }
}
