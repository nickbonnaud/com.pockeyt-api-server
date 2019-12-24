<?php

namespace Tests\Unit\Customer;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoyaltyCardTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_a_loyalty_card_belongs_to_a_customer() {
  	$customer = factory(\App\Models\Customer\Customer::class)->create();
  	$loyaltyCard = factory(\App\Models\Customer\LoyaltyCard::class)->create(['customer_id' => $customer->id]);
  	$this->assertInstanceOf('App\Models\Customer\LoyaltyCard', $customer->loyaltyCard);
  }

  public function test_a_customer_has_one_loyalty_card() {
  	$customer = factory(\App\Models\Customer\Customer::class)->create();
  	$loyaltyCard = factory(\App\Models\Customer\LoyaltyCard::class)->create(['customer_id' => $customer->id]);
  	$this->assertInstanceOf('App\Models\Customer\Customer', $loyaltyCard->customer);
  }

  public function test_a_loyalty_card_belongs_to_a_loyalty_program() {
  	$loyaltyProgram = factory(\App\Models\Business\LoyaltyProgram::class)->create();
  	$loyaltyCard = factory(\App\Models\Customer\LoyaltyCard::class)->create(['loyalty_program_id' => $loyaltyProgram->id]);
  	$this->assertInstanceOf('App\Models\Customer\LoyaltyCard', $loyaltyProgram->loyaltyCards->first());
  }

  public function test_a_loyalty_program_can_have_multiple_loyalty_cards() {
  	$loyaltyProgram = factory(\App\Models\Business\LoyaltyProgram::class)->create();
  	$loyaltyCard = factory(\App\Models\Customer\LoyaltyCard::class, 10)->create(['loyalty_program_id' => $loyaltyProgram->id]);
  	$this->assertEquals(10, $loyaltyProgram->loyaltyCards->count());
  }

  public function test_a_loyalty_card_has_one_loyalty_program() {
  	$loyaltyProgram = factory(\App\Models\Business\LoyaltyProgram::class)->create();
  	$loyaltyCard = factory(\App\Models\Customer\LoyaltyCard::class)->create(['loyalty_program_id' => $loyaltyProgram->id]);
  	$this->assertInstanceOf('App\Models\Business\LoyaltyProgram', $loyaltyCard->loyaltyProgram);
  }
}
