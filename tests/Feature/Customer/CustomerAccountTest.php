<?php

namespace Tests\Feature\Customer;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerAccountTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unauth_customer_cannot_update_account_data() {
    $account = factory(\App\Models\Customer\CustomerAccount::class)->create();

    $attributes = [
      'tip_rate' => 20
    ];

    $response = $this->json('PATCH', "/api/customer/account/{$account->identifier}", $attributes)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_customer_must_provide_correct_tip_account_data() {
    $account = factory(\App\Models\Customer\CustomerAccount::class)->create();
    $headers = $this->customerHeaders($account->customer);
    $attributes = [
      'tip_rate' => -10
    ];

    $response = $this->json('PATCH', "/api/customer/account/{$account->identifier}", $attributes, $headers)->assertStatus(422);
    $this->assertEquals('The tip rate must be at least 0.', ($response->getData())->errors->tip_rate[0]);

    $attributes = [
      'tip_rate' => 80
    ];

    $response = $this->json('PATCH', "/api/customer/account/{$account->identifier}", $attributes, $headers)->assertStatus(422);
    $this->assertEquals('The tip rate may not be greater than 50.', ($response->getData())->errors->tip_rate[0]);

    $attributes = [
      'tip_rate' => 'not int'
    ];

    $response = $this->json('PATCH', "/api/customer/account/{$account->identifier}", $attributes, $headers)->assertStatus(422);
    $this->assertEquals('The tip rate must be an integer.', ($response->getData())->errors->tip_rate[0]);
  }

  public function test_an_auth_customer_must_provide_correct_primary_account_data() {
    $account = factory(\App\Models\Customer\CustomerAccount::class)->create();
    $headers = $this->customerHeaders($account->customer);
    $attributes = [
      'primary' => 'not_choice'
    ];

    $response = $this->json('PATCH', "/api/customer/account/{$account->identifier}", $attributes, $headers)->assertStatus(422);
    $this->assertEquals('The selected primary is invalid.', ($response->getData())->errors->primary[0]);

    $attributes = [
      'primary' => 80
    ];

    $response = $this->json('PATCH', "/api/customer/account/{$account->identifier}", $attributes, $headers)->assertStatus(422);
    $this->assertEquals('The primary must be a string.', ($response->getData())->errors->primary[0]);
  }

  public function test_an_auth_customer_must_provide_account_data() {
    $account = factory(\App\Models\Customer\CustomerAccount::class)->create();
    $headers = $this->customerHeaders($account->customer);
    $attributes = [];

    $response = $this->json('PATCH', "/api/customer/account/{$account->identifier}", $attributes, $headers)->assertStatus(422);
    $this->assertEquals('The primary field is required when tip rate is not present.', ($response->getData())->errors->primary[0]);
    $this->assertEquals('The tip rate field is required when primary is not present.', ($response->getData())->errors->tip_rate[0]);
  }

  public function test_an_auth_customer_can_change_their_tip_rate() {
    $account = factory(\App\Models\Customer\CustomerAccount::class)->create(['tip_rate' => 15]);
    $headers = $this->customerHeaders($account->customer);
    $newTipRate = 20;
    $attributes = [
      'tip_rate' => $newTipRate
    ];

    $response = $this->json('PATCH', "/api/customer/account/{$account->identifier}", $attributes, $headers)->getData();
    $this->assertEquals($newTipRate, $response->data->tip_rate);
    $this->assertDatabaseHas('customer_accounts', ['customer_id' => $account->customer->id, 'tip_rate' => $newTipRate]);
  }

  public function test_an_auth_customer_can_change_their_primary_payment_method() {
    $account = factory(\App\Models\Customer\CustomerAccount::class)->create(['primary' => 'ach']);
    $headers = $this->customerHeaders($account->customer);
    $newPrimary = 'card';
    $attributes = [
      'primary' => $newPrimary
    ];

    $response = $this->json('PATCH', "/api/customer/account/{$account->identifier}", $attributes, $headers)->getData();
    $this->assertEquals($newPrimary, $response->data->primary);
    $this->assertDatabaseHas('customer_accounts', ['customer_id' => $account->customer->id, 'primary' => $newPrimary]);
  }
}
