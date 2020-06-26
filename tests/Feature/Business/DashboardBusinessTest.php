<?php

namespace Tests\Feature\Business;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Notification;

class DashboardBusinessTest extends TestCase {

  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
    $this->artisan('db:seed', ['--class' => 'MasterTestSeeder']);
  }


  public function test_an_unauth_business_cannot_retrieve_business_resource() {
    Notification::fake();
    $business = \App\Models\Business\Business::first();

    $response = $this->json('GET', '/api/business/me')->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_business_can_retrieve_their_business_resource() {
    $business = \App\Models\Business\Business::first();
    $this->businessHeaders($business);

    $response = $this->json('GET', '/api/business/me')->getData();
    $this->assertNotNull($response->data->profile);
    $this->assertNotNull($response->data->photos);
    $this->assertNotNull($response->data->accounts->business_account);
    $this->assertNotNull($response->data->accounts->owner_accounts);
    $this->assertNotNull($response->data->accounts->bank_account);
    $this->assertNotNull($response->data->location);
    $this->assertNotNull($response->data->pos_account);
  }
}
