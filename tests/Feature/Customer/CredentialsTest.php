<?php

namespace Tests\Feature\Customer;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CredentialsTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unauth_customer_cannot_fetch_api_credentials() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();

    $response = $this->json('GET', 'api/customer/credentials')->assertUnauthorized();
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_customer_can_fetch_credentials() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $this->customerHeaders($customer);

    $response = $this->json('GET', 'api/customer/credentials')->assertStatus(200);
    $this->assertNotNull($response->getData()->data->dwolla_key);
  }
}
