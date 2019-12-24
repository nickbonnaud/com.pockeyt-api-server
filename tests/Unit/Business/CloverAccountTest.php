<?php

namespace Tests\Unit\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CloverAccountTest extends TestCase {
	use WithFaker, RefreshDatabase;

	public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

	public function test_a_clover_account_creates_an_identifier() {
		$posAccount = factory(\App\Models\Business\PosAccount::class)->create();
		$cloverAccount = factory(\App\Models\Business\CloverAccount::class)->create(['pos_account_id' => $posAccount->id]);
		$this->assertNotNull($cloverAccount->identifier);
	}

	public function test_a_clover_account_belongs_to_a_pos_account() {
		$posAccount = factory(\App\Models\Business\PosAccount::class)->create();
		$cloverAccount = factory(\App\Models\Business\CloverAccount::class)->create(['pos_account_id' => $posAccount->id]);
		$this->assertInstanceOf('App\Models\Business\CloverAccount', $posAccount->cloverAccount);
	}

	public function test_a_pos_account_has_one_clover_account() {
		$posAccount = factory(\App\Models\Business\PosAccount::class)->create();
		$cloverAccount = factory(\App\Models\Business\CloverAccount::class)->create(['pos_account_id' => $posAccount->id]);
		$this->assertInstanceOf('App\Models\Business\PosAccount', $cloverAccount->posAccount);
	}
}
