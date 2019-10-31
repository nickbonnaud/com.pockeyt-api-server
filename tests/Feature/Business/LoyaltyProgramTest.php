<?php

namespace Tests\Feature\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoyaltyProgramTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function test_an_unauthorized_business_cannot_create_loyalty_program() {
  	$business = factory(\App\Models\Business\Business::class)->create();
  	$attributes = [
  		'purchases_required' => 20,
  		'reward' => 'Large Ice Cream'
  	];

  	$response = $this->json('POST', '/api/business/loyalty-program', $attributes)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_authorized_business_can_create_loyalty_program() {
  	$business = factory(\App\Models\Business\Business::class)->create();
  	$header = $this->businessHeaders($business);
  	$attributes = [
  		'purchases_required' => 20,
  		'reward' => 'Large Ice Cream'
  	];

  	$response = $this->json('POST', '/api/business/loyalty-program', $attributes, $header)->getData();
    $this->assertDatabaseHas('loyalty_programs', ['identifier' => $response->data->identifier, 'reward' => 'Large Ice Cream']);
    $this->assertEquals(20, $response->data->purchases_required);
  }

  public function test_correct_data_must_be_provided_to_create_loyalty_program() {
  	$business = factory(\App\Models\Business\Business::class)->create();
  	$header = $this->businessHeaders($business);
  	$attributes = [
  		'purchases_required' => 20,
  		'amount_required' => 10000,
  		'reward' => 'Large Ice Cream'
  	];

  	$response = $this->json('POST', '/api/business/loyalty-program', $attributes, $header)->assertStatus(422);
  	$response = $response->getData();
    $this->assertEquals('Can only have number of purchases requirement or total amount spent requirement.', $response->errors->purchases_required[0]);
  }

  public function test_an_unauth_user_cannot_retrieve_loyalty_program() {
  	factory(\App\Models\Business\LoyaltyProgram::class)->create();
  	$response = $this->json('GET', '/api/business/loyalty-program')->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_user_can_retrieve_loyalty_program() {
  	$loyaltyProgram = factory(\App\Models\Business\LoyaltyProgram::class)->create();
  	$header = $this->businessHeaders($loyaltyProgram->business);
  	$response = $this->json('GET', '/api/business/loyalty-program', $header)->getData();
  	$this->assertEquals($loyaltyProgram->reward, $response->data->reward);
  }

  public function test_an_unauth_user_cannot_delete_loyalty_program() {
  	$loyaltyProgram = factory(\App\Models\Business\LoyaltyProgram::class)->create();
  	$response = $this->json('DELETE', "/api/business/loyalty-program/{$loyaltyProgram->identifier}")->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_user_cannot_delete_loyalty_program_not_theirs() {
  	$loyaltyProgramOne = factory(\App\Models\Business\LoyaltyProgram::class)->create();
  	$loyaltyProgramTwo = factory(\App\Models\Business\LoyaltyProgram::class)->create();

  	$header = $this->businessHeaders($loyaltyProgramOne->business);

  	$response = $this->json('DELETE', "/api/business/loyalty-program/{$loyaltyProgramTwo->identifier}")->assertStatus(403);
    $this->assertEquals('Permission denied.', ($response->getData())->errors);
  }

  public function test_an_auth_user_can_delete_loyalty_program() {
  	$loyaltyProgram = factory(\App\Models\Business\LoyaltyProgram::class)->create();

  	$header = $this->businessHeaders($loyaltyProgram->business);

  	$response = $this->json('DELETE', "/api/business/loyalty-program/{$loyaltyProgram->identifier}")->assertStatus(200);
    $this->assertEquals('Loyalty program deleted.', ($response->getData())->success);
  }
}
