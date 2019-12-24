<?php

namespace Tests\Unit\Transaction;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionTest extends TestCase {
	use WithFaker, RefreshDatabase;

	public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

	public function test_a_transaction_creates_a_unique_identifier() {
		$transaction = factory(\App\Models\Transaction\Transaction::class)->create();
		$this->assertNotNull($transaction->identifier);
	}

	public function test_a_transaction_belongs_to_a_customer() {
		$transaction = factory(\App\Models\Transaction\Transaction::class)->create();
		$customer = $transaction->customer;
		$this->assertInstanceOf('App\Models\Transaction\Transaction', $customer->transactions->first());
	}

	public function test_a_customer_has_many_transactions() {
		$customer = factory(\App\Models\Customer\Customer::class)->create();
		$customerProfile = factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' =>$customer->id]);
		$business = factory(\App\Models\Business\Business::class)->create();
		factory(\App\Models\Business\AccountStatus::class)->create(['name' => 'incomplete']);
		$account = factory(\App\Models\Business\Account::class)->create(['business_id' => $business->id]);
		$payFacAccount = factory(\App\Models\Business\PayFacAccount::class)->create(['account_id' => $account->id]);
		factory(\App\Models\Business\PayFacBusiness::class)->create(['pay_fac_account_id' => $payFacAccount->id]);
		$posAccount = factory(\App\Models\Business\PosAccount::class)->create(['business_id' => $business->id]);
		factory(\App\Models\Business\SquareAccount::class)->create(['pos_account_id' => $posAccount->id]);
		factory(\App\Models\Business\CloverAccount::class)->create(['pos_account_id' => $posAccount->id]);

		$transaction = factory(\App\Models\Transaction\Transaction::class, 2)->create(['customer_id' => $customer->id]);
		$this->assertEquals(2, $customer->transactions->count());
	}

	public function test_a_transaction_has_one_customer() {
		$transaction = factory(\App\Models\Transaction\Transaction::class)->create();
		$this->assertInstanceOf('App\Models\Customer\Customer', $transaction->customer);
	}

	public function test_a_transaction_belongs_to_a_business() {
		$transaction = factory(\App\Models\Transaction\Transaction::class)->create();
		$business = $transaction->business;
		$this->assertInstanceOf('App\Models\Transaction\Transaction', $business->transactions->first());
	}

	public function test_a_business_has_many_transactions() {
		$customer = factory(\App\Models\Customer\Customer::class)->create();
		$customerProfile = factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' =>$customer->id]);
		$business = factory(\App\Models\Business\Business::class)->create();
		factory(\App\Models\Business\AccountStatus::class)->create(['name' => 'incomplete']);
		$account = factory(\App\Models\Business\Account::class)->create(['business_id' => $business->id]);
		$payFacAccount = factory(\App\Models\Business\PayFacAccount::class)->create(['account_id' => $account->id]);
		factory(\App\Models\Business\PayFacBusiness::class)->create(['pay_fac_account_id' => $payFacAccount->id]);
		$posAccount = factory(\App\Models\Business\PosAccount::class)->create(['business_id' => $business->id]);
		factory(\App\Models\Business\SquareAccount::class)->create(['pos_account_id' => $posAccount->id]);
		factory(\App\Models\Business\CloverAccount::class)->create(['pos_account_id' => $posAccount->id]);

		$transaction = factory(\App\Models\Transaction\Transaction::class, 2)->create(['business_id' => $business->id]);
		$this->assertEquals(2, $business->transactions->count());
	}

	public function test_a_tranasaction_has_one_business() {
		$transaction = factory(\App\Models\Transaction\Transaction::class)->create();
		$this->assertInstanceOf('App\Models\Business\Business', $transaction->business);
	}
}
