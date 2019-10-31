<?php

namespace Tests\Unit\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoyaltyProgramTest extends TestCase {
	use WithFaker, RefreshDatabase;

	public function test_a_loyalty_program_belongs_to_a_business() {
		$business = factory(\App\Models\Business\Business::class)->create();
		$loyaltyProgram = factory(\App\Models\Business\LoyaltyProgram::class)->create(['business_id' => $business->id]);
		$this->assertInstanceOf('App\Models\Business\LoyaltyProgram', $business->loyaltyProgram);
	}

	public function test_a_business_has_one_loyalty_program() {
		$business = factory(\App\Models\Business\Business::class)->create();
		$loyaltyProgram = factory(\App\Models\Business\LoyaltyProgram::class)->create(['business_id' => $business->id]);
		$this->assertInstanceOf('App\Models\Business\Business', $loyaltyProgram->business);
	}

	public function test_a_loyalty_program_auto_creates_rewards_earned_outstanding_rewards() {
		$business = factory(\App\Models\Business\Business::class)->create();
		$loyaltyProgram = factory(\App\Models\Business\LoyaltyProgram::class)->create(['business_id' => $business->id]);
		$this->assertEquals(0, $loyaltyProgram->total_rewards_earned);
		$this->assertEquals(0, $loyaltyProgram->outstanding_rewards);
	}

	public function test_a_loyalty_program_creates_an_identifier() {
		$business = factory(\App\Models\Business\Business::class)->create();
		$loyaltyProgram = factory(\App\Models\Business\LoyaltyProgram::class)->create(['business_id' => $business->id]);
		$this->assertNotNull($loyaltyProgram->identifier);
	}
}
