<?php

namespace Tests\Unit\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PayFacBankTest extends TestCase {
	use WithFaker, RefreshDatabase;

	public function test_a_pay_fac_bank_creates_a_unique_identifier() {
		factory(\App\Models\Business\AccountStatus::class)->create();
		$payFacBank = factory(\App\Models\Business\PayFacBank::class)->create();
		$this->assertNotNull($payFacBank->identifier);
	}

	public function test_a_pay_fac_routing_number_is_auto_encrypted() {
		factory(\App\Models\Business\AccountStatus::class)->create();
		$routingNumber = $this->faker->randomNumber($nbDigits = 9, $strict = true);
		$payFacBank = factory(\App\Models\Business\PayFacBank::class)->create(['routing_number' => $routingNumber]);
		$this->assertDatabaseHas('pay_fac_banks', ['state' => $payFacBank->state, 'address' => $payFacBank->address]);
		$this->assertDatabaseMissing('pay_fac_banks', ['state' => $payFacBank->state, 'address' => $payFacBank->address, 'routing_number' => $routingNumber]);
		$this->assertNotEquals($routingNumber, $payFacBank->getOriginal('routing_number'));
	}

	public function test_a_pay_fac_routing_number_is_auto_decrypted() {
		factory(\App\Models\Business\AccountStatus::class)->create();
		$routingNumber = $this->faker->randomNumber($nbDigits = 9, $strict = true);
		$payFacBank = factory(\App\Models\Business\PayFacBank::class)->create(['routing_number' => $routingNumber]);
		$this->assertEquals($routingNumber, $payFacBank->routing_number);
		$this->assertNotEquals($routingNumber, $payFacBank->getOriginal('routing_number'));
	}

	public function test_a_pay_fac_bank_account_number_is_encrypted() {
		factory(\App\Models\Business\AccountStatus::class)->create();
		$accountNumber = $this->faker->randomNumber($nbDigits = 9, $strict = true);
		$payFacBank = factory(\App\Models\Business\PayFacBank::class)->create(['account_number' => $accountNumber]);
		$this->assertDatabaseHas('pay_fac_banks', ['state' => $payFacBank->state, 'address' => $payFacBank->address]);
		$this->assertDatabaseMissing('pay_fac_banks', ['state' => $payFacBank->state, 'address' => $payFacBank->address, 'account_number' => $accountNumber]);
		$this->assertNotEquals($accountNumber, $payFacBank->getOriginal('account_number'));
	}

	public function test_a_pay_fac_account_number_is_auto_decrypted() {
		factory(\App\Models\Business\AccountStatus::class)->create();
		$accountNumber = $this->faker->randomNumber($nbDigits = 9, $strict = true);
		$payFacBank = factory(\App\Models\Business\PayFacBank::class)->create(['account_number' => $accountNumber]);
		$this->assertEquals($accountNumber, $payFacBank->account_number);
		$this->assertNotEquals($accountNumber, $payFacBank->getOriginal('account_number'));
	}

	public function test_a_pay_fac_bank_belongs_to_a_pay_fac_account() {
		factory(\App\Models\Business\AccountStatus::class)->create();
		$payFacAccount = factory(\App\Models\Business\PayFacAccount::class)->create();
		$payFacBank = factory(\App\Models\Business\PayFacBank::class)->create(['pay_fac_account_id' => $payFacAccount->id]);
		$this->assertInstanceOf('App\Models\Business\PayFacBank', $payFacAccount->payFacBank);
	}

	public function test_a_pay_fac_account_has_one_pay_fac_bank() {
		factory(\App\Models\Business\AccountStatus::class)->create();
		$payFacAccount = factory(\App\Models\Business\PayFacAccount::class)->create();
		$payFacBank = factory(\App\Models\Business\PayFacBank::class)->create(['pay_fac_account_id' => $payFacAccount->id]);
		$this->assertInstanceOf('App\Models\Business\PayFacAccount', $payFacBank->payFacAccount);
	}
}
