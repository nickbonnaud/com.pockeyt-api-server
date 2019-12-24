<?php

namespace Tests\Feature\Customer;

use Tests\TestCase;
use App\Models\Customer\PushToken;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PushTokenTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unauth_customer_cannot_create_a_push_token() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $attributes = [
      'device' => 'ios',
      'token' => 'ncduiwhu82r9aobcb'
    ];

    $response = $this->json('POST', '/api/customer/push-token', $attributes)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_customer_must_submit_correct_token_data() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $headers = $this->customerHeaders($customer);
    $attributes = [
      'device' => 'not_device',
      'token' => 'cbbdshvcd374fhu'
    ];

    $response = $this->json('POST', '/api/customer/push-token', $attributes, $headers)->assertStatus(422);
    $this->assertEquals('The selected device is invalid.', ($response->getData())->errors->device[0]);
    $this->assertEquals('The token must be at least 64 characters.', ($response->getData())->errors->token[0]);
  }

  public function test_an_auth_customer_can_create_a_push_token() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $token = factory(\App\Models\Customer\PushToken::class)->make();
    $headers = $this->customerHeaders($customer);

    $response = $this->json('POST', '/api/customer/push-token', $token->toArray(), $headers)->getData();
    $this->assertEquals($token['device'], $response->data->device);
    $this->assertEquals($token['token'], $response->data->token);
    $this->assertDataBaseHas('push_tokens', ['customer_id' => $customer->id, 'token' => $token['token']]);
  }

  public function test_updating_token_replaces_old_token() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $token = factory(\App\Models\Customer\PushToken::class)->create(['customer_id' => $customer->id]);
    $headers = $this->customerHeaders($customer);

    $newToken = factory(\App\Models\Customer\PushToken::class)->make();
    $newToken->token = $newToken->token . 'new';
    $response = $this->json('POST', '/api/customer/push-token', $newToken->toArray(), $headers)->getData();

    $this->assertEquals($newToken['token'], $response->data->token);
    $this->assertDataBaseHas('push_tokens', ['customer_id' => $customer->id, 'token' => $newToken['token']]);
    $this->assertDataBaseMissing('push_tokens', ['customer_id' => $customer->id, 'token' => $token['token']]);
    $this->assertEquals(1, PushToken::count());
  }
}
