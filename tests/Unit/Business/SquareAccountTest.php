<?php

namespace Tests\Unit\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SquareAccountTest extends TestCase {
	use WithFaker, RefreshDatabase;

	public function test_a_square_account_creates_an_identifier() {
		$posAccount = $this->createRequiredAccounts();
		$squareAccount = factory(\App\Models\Business\SquareAccount::class)->create(['pos_account_id' => $posAccount->id]);
		$this->assertNotNull($squareAccount->identifier);
	}

	public function test_a_square_account_belongs_to_a_pos_account() {
		$posAccount = $this->createRequiredAccounts();
		$squareAccount = factory(\App\Models\Business\SquareAccount::class)->create(['pos_account_id' => $posAccount->id]);
		$this->assertInstanceOf('App\Models\Business\SquareAccount', $posAccount->squareAccount);
	}

	public function test_a_pos_account_has_one_square_account() {
		$posAccount = $this->createRequiredAccounts();
		$squareAccount = factory(\App\Models\Business\SquareAccount::class)->create(['pos_account_id' => $posAccount->id]);
		$this->assertInstanceOf('App\Models\Business\PosAccount', $squareAccount->posAccount);
	}


	private function createRequiredAccounts() {
    factory(\App\Models\Business\AccountStatus::class)->create();
    $posStatus = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $posStatus->id]);
    $account = factory(\App\Models\Business\Account::class)->create(['business_id' => $posAccount->business->id]);
    $payFacAccount = factory(\App\Models\Business\PayFacAccount::class)->create(['account_id' => $account->id]);
    $payFacBusinessAccount = factory(\App\Models\Business\PayFacBusiness::class)->create(['pay_fac_account_id' => $payFacAccount->id]);
    factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'closed']);
    return $posAccount;
  }
}
