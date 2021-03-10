<?php

namespace Tests\Feature\Business;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CredentialsTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unauth_business_cannot_fetch_api_credentials() {
    $business = factory(\App\Models\Business\Business::class)->create();

    $response = $this->json('GET', '/api/business/credentials')->assertUnauthorized();
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_business_can_fetch_credentials() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $token = $this->createBusinessToken($business);
    $response = $this->send($token, 'get', '/api/business/credentials')->assertStatus(200);

    $this->assertNotNull($response->getData()->data->google_key);
  }
}
