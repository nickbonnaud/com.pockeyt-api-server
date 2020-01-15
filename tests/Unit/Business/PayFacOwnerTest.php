<?php

namespace Tests\Unit\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PayFacOwnerTest extends TestCase {
	use WithFaker, RefreshDatabase;

	public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

	public function test_a_pay_fac_owner_creates_a_unique_identifier() {
    factory(\App\Models\Business\AccountStatus::class)->create();
    $payFacOwner = factory(\App\Models\Business\PayFacOwner::class)->create();
    $this->assertNotNull($payFacOwner->identifier);
  }

	public function test_a_pay_fac_owner_belongs_to_a_pay_fac_account() {
		factory(\App\Models\Business\AccountStatus::class)->create();
    $payFacAccount = factory(\App\Models\Business\PayFacAccount::class)->create();
    $payFacOwner = factory(\App\Models\Business\PayFacOwner::class)->create(['pay_fac_account_id' => $payFacAccount->id]);
    $this->assertInstanceOf('App\Models\Business\PayFacOwner', $payFacAccount->payFacOwners->first());
	}

	public function test_a_pay_fac_account_has_many_pay_fac_owners(){
		factory(\App\Models\Business\AccountStatus::class)->create();
    $payFacAccount = factory(\App\Models\Business\PayFacAccount::class)->create();
    $payFacOwner = factory(\App\Models\Business\PayFacOwner::class)->create(['pay_fac_account_id' => $payFacAccount->id]);
    $this->assertInstanceOf('App\Models\Business\PayFacAccount', $payFacOwner->payFacAccount);
	}

	public function test_a_pay_fac_account_can_have_multiple_owners() {
		factory(\App\Models\Business\AccountStatus::class)->create();
    $payFacAccount = factory(\App\Models\Business\PayFacAccount::class)->create();
    factory(\App\Models\Business\PayFacOwner::class, 2)->create(['pay_fac_account_id' => $payFacAccount->id]);

    $this->assertEquals(2, $payFacAccount->payFacOwners->count());
	}

	public function test_pay_fac_owner_ssn_is_automatically_encrypted() {
		factory(\App\Models\Business\AccountStatus::class)->create();
		$ssn = $this->faker->ssn;
		$owner = factory(\App\Models\Business\PayFacOwner::class)->create(['ssn' => $ssn]);
		$this->assertDatabaseHas('pay_fac_owners', ['first_name' => $owner->first_name, 'last_name' => $owner->last_name]);
		$this->assertDatabaseMissing('pay_fac_owners', ['first_name' => $owner->first_name, 'last_name' => $owner->last_name, 'ssn' => $ssn]);
	}

	public function test_pay_fac_owner_ssn_is_automatically_decrypted_when_accessed() {
		factory(\App\Models\Business\AccountStatus::class)->create();
		$ssn = $this->faker->ssn;
		$owner = factory(\App\Models\Business\PayFacOwner::class)->create(['ssn' => $ssn]);
		$this->assertEquals($ssn, $owner->ssn);
		$this->assertNotEquals($ssn, $owner->getOriginal('ssn'));
	}

	public function test_owner_percent_ownership_is_auto_multiplied_by_hundred() {
		factory(\App\Models\Business\AccountStatus::class)->create();
		$percentOwn = 85;
		$owner = factory(\App\Models\Business\PayFacOwner::class)->create(['percent_ownership' => $percentOwn]);
		$this->assertDatabaseHas('pay_fac_owners', ['first_name' => $owner->first_name, 'last_name' => $owner->last_name, 'percent_ownership' => $percentOwn * 100]);
		$this->assertDatabaseMissing('pay_fac_owners', ['first_name' => $owner->first_name, 'last_name' => $owner->last_name, 'percent_ownership' => $percentOwn]);
	}

	public function test_owner_percent_ownership_reduced_by_hundred_when_accessed() {
		factory(\App\Models\Business\AccountStatus::class)->create();
		$percentOwn = 85;
		$owner = factory(\App\Models\Business\PayFacOwner::class)->create(['percent_ownership' => $percentOwn]);
		$this->assertEquals($percentOwn, $owner->percent_ownership);
		$this->assertNotEquals($percentOwn, $owner->getOriginal('percent_ownership'));
	}

	public function test_payfac_owners_must_have_a_primary_to_set_status_to_104() {
		$owner = factory(\App\Models\Business\PayFacOwner::class)->create(['primary' => false]);
		$this->assertNotEquals(104, $owner->payFacAccount->account->status->code);
	}

	public function test_payfac_owners_with_a_primary_sets_status_to_104() {
		$owner = factory(\App\Models\Business\PayFacOwner::class)->create(['primary' => true]);
		$this->assertEquals(104, $owner->payFacAccount->account->status->code);
	}
}
