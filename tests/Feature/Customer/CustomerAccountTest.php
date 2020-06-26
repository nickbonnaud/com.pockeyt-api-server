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
      'tip_rate' => 20,
      'quick_tip_rate' => 7
    ];

    $response = $this->json('PATCH', "/api/customer/account/{$account->identifier}", $attributes)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_customer_must_provide_correct_tip_account_data() {
    $account = factory(\App\Models\Customer\CustomerAccount::class)->create();
    $headers = $this->customerHeaders($account->customer);
    $attributes = [
      'tip_rate' => -10,
      'quick_tip_rate' => -7
    ];

    $response = $this->json('PATCH', "/api/customer/account/{$account->identifier}", $attributes, $headers)->assertStatus(422);
    $this->assertEquals('The tip rate must be at least 0.', ($response->getData())->errors->tip_rate[0]);
    $this->assertEquals('The quick tip rate must be at least 0.', ($response->getData())->errors->quick_tip_rate[0]);

    $attributes = [
      'tip_rate' => 80,
      'quick_tip_rate' => 40
    ];

    $response = $this->json('PATCH', "/api/customer/account/{$account->identifier}", $attributes, $headers)->assertStatus(422);
    $this->assertEquals('The tip rate may not be greater than 30.', ($response->getData())->errors->tip_rate[0]);
    $this->assertEquals('The quick tip rate may not be greater than 30.', ($response->getData())->errors->quick_tip_rate[0]);

    $attributes = [
      'tip_rate' => 'not int',
      'quick_tip_rate' => 'not int'
    ];

    $response = $this->json('PATCH', "/api/customer/account/{$account->identifier}", $attributes, $headers)->assertStatus(422);
    $this->assertEquals('The tip rate must be an integer.', ($response->getData())->errors->tip_rate[0]);
    $this->assertEquals('The quick tip rate must be an integer.', ($response->getData())->errors->quick_tip_rate[0]);
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

    $response = $this->json('PATCH', "/api/customer/account/{$account->identifier}", $attributes, $headers)->assertStatus(403);
    $this->assertEquals('Permission denied.', ($response->getData())->errors);
  }

  public function test_an_auth_customer_can_change_their_tip_rate() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $headers = $this->customerHeaders($customer);
    $newTipRate = 20;
    $attributes = [
      'tip_rate' => $newTipRate
    ];

    $response = $this->json('PATCH', "/api/customer/account/{$customer->account->identifier}", $attributes, $headers)->getData();
    $this->assertEquals($newTipRate, $response->data->account->tip_rate);
    $this->assertDatabaseHas('customer_accounts', ['customer_id' => $customer->account->customer->id, 'tip_rate' => $newTipRate]);
  }

  public function test_an_auth_customer_can_change_their_quick_tip_rate() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $headers = $this->customerHeaders($customer);
    $newTipRate = 7;
    $attributes = [
      'quick_tip_rate' => $newTipRate
    ];

    $response = $this->json('PATCH', "/api/customer/account/{$customer->account->identifier}", $attributes, $headers)->getData();
    $this->assertEquals($newTipRate, $response->data->account->quick_tip_rate);
    $this->assertDatabaseHas('customer_accounts', ['customer_id' => $customer->account->customer->id, 'quick_tip_rate' => $newTipRate]);
  }

  public function test_an_auth_customer_can_change_both_tip_rates() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $headers = $this->customerHeaders($customer);
    $tipRate = 17;
    $quickTipRate = 7;
    $attributes = [
      'tip_rate' => $tipRate,
      'quick_tip_rate' => $quickTipRate
    ];

    $response = $this->json('PATCH', "/api/customer/account/{$customer->account->identifier}", $attributes, $headers)->getData();
    $this->assertEquals($quickTipRate, $response->data->account->quick_tip_rate);
    $this->assertEquals($tipRate, $response->data->account->tip_rate);
    $this->assertDatabaseHas('customer_accounts', ['customer_id' => $customer->account->customer->id, 'quick_tip_rate' => $quickTipRate]);
    $this->assertDatabaseHas('customer_accounts', ['customer_id' => $customer->account->customer->id, 'tip_rate' => $tipRate]);
  }

  public function test_an_auth_customer_setting_tip_rate_first_time_changes_customer_status() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $customer->setStatus(102);

    $headers = $this->customerHeaders($customer);
    $tipRate = 17;
    $quickTipRate = 7;
    $attributes = [
      'tip_rate' => $tipRate,
      'quick_tip_rate' => $quickTipRate
    ];

    $response = $this->json('PATCH', "/api/customer/account/{$customer->account->identifier}", $attributes, $headers)->getData();
    $this->assertEquals(103, $response->data->status->code);
  }

  public function test_an_auth_customer_can_change_their_primary_payment_method() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $headers = $this->customerHeaders($customer);
    $newPrimary = 'card';
    $attributes = [
      'primary' => $newPrimary
    ];

    $response = $this->json('PATCH', "/api/customer/account/{$customer->account->identifier}", $attributes, $headers)->getData();
    $this->assertEquals($newPrimary, $response->data->account->primary);
    $this->assertDatabaseHas('customer_accounts', ['customer_id' => $customer->account->customer->id, 'primary' => $newPrimary]);
  }
}
