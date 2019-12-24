<?php

namespace Tests\Feature\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PosAccountTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unauth_business_cannot_create_pos_account() {
    factory(\App\Models\Business\PosAccountStatus::class)->create();
    $business = factory(\App\Models\Business\Business::class)->create();

    $attributes = [
      'type' => 'square',
      'takes_tips' => true,
      'allows_open_tickets' => false
    ];

    $response = $this->json('POST', '/api/business/pos/account', $attributes)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_a_business_must_post_correct_type_to_create_pos_account() {
    factory(\App\Models\Business\PosAccountStatus::class)->create();
    $business = factory(\App\Models\Business\Business::class)->create();
    $headers = $this->businessHeaders($business);

    $attributes = [
      'type' => 'not_type',
      'takes_tips' => true,
      'allows_open_tickets' => false
    ];

    $response = $this->json('POST', '/api/business/pos/account', $attributes, $headers)->assertStatus(422);
    $this->assertEquals('The selected type is invalid.', ($response->getData())->errors->type[0]);
  }

  public function test_a_business_can_create_a_pos_account() {
    factory(\App\Models\Business\PosAccountStatus::class)->create();
    $business = factory(\App\Models\Business\Business::class)->create();
    $headers = $this->businessHeaders($business);

    $attributes = [
      'type' => 'square',
      'takes_tips' => true,
      'allows_open_tickets' => false
    ];

    $response = $this->json('POST', '/api/business/pos/account', $attributes, $headers)->getData();
    $this->assertDatabaseHas('pos_accounts', ['business_id' => $business->id, 'type' => $response->data->type]);
    $this->assertEquals(100, $response->data->status->code);
  }

  public function  test_an_unauth_business_cannot_retrieve_pos_account() {
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create();
    $response = $this->json('GET', '/api/business/pos/account');
    $response->assertStatus(401);
    $this->assertEquals('Unauthenticated.', $response->getData()->message);
  }

  public function test_a_business_can_retrieve_their_pos_account() {
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create();
    $headers = $this->businessHeaders($posAccount->business);
    $response = $this->json('GET', '/api/business/pos/account', $headers);
    $response->assertStatus(200);
    $this->assertEquals(100, $response->getData()->data->status->code);
  }

  public function test_an_unauth_business_cannot_update_pos_account() {
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create();

    $formData = [
      'type' => 'clover',
      'takes_tips' => !$posAccount->takes_tips,
      'allows_open_tickets' => !$posAccount->allows_open_tickets
    ];

    $response = $this->json('PATCH', "/api/business/pos/account/{$posAccount->identifier}", $formData)->assertStatus(401);

    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_a_business_can_update_their_pos_account() {
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create();
    $this->businessHeaders($posAccount->business);

    $formData = [
      'type' => 'clover',
      'takes_tips' => !$posAccount->takes_tips,
      'allows_open_tickets' => !$posAccount->allows_open_tickets
    ];

    $this->assertDatabaseMissing('pos_accounts', 
      [
        'identifier' => $posAccount->identifier, 
        'type' =>$formData['type'],
        'takes_tips' => $formData['takes_tips'],
        'allows_open_tickets' => $formData['allows_open_tickets']
      ]
    );

    $response = $this->json('PATCH', "/api/business/pos/account/{$posAccount->identifier}", $formData)->getData();
    $this->assertDatabaseHas('pos_accounts', 
      [
        'identifier' => $posAccount->identifier, 
        'type' =>$formData['type'],
        'takes_tips' => $formData['takes_tips'],
        'allows_open_tickets' => $formData['allows_open_tickets']
      ]
    );

    $this->assertEquals($formData['type'], ($response->data->type));
    $this->assertEquals($formData['takes_tips'], ($response->data->takes_tips));
  }
}
