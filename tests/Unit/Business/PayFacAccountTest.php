<?php

namespace Tests\Unit\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PayFacAccountTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function test_a_payfac_account_creates_a_unique_identifier() {
    factory(\App\Models\Business\AccountStatus::class)->create();
    $account = factory(\App\Models\Business\PayFacAccount::class)->create();
    $this->assertNotNull($account->identifier);
  }

  public function test_a_pay_fac_account_belongs_to_an_account() {
  	factory(\App\Models\Business\AccountStatus::class)->create();
    $account = factory(\App\Models\Business\Account::class)->create();
  	$payFacAccount = factory(\App\Models\Business\PayFacAccount::class)->create(['account_id' => $account->id]);
  	$this->assertInstanceOf('App\Models\Business\PayFacAccount', $account->payFacAccount);
  }

  public function test_an_account_has_one_pay_fac_account() {
  	factory(\App\Models\Business\AccountStatus::class)->create();
    $account = factory(\App\Models\Business\Account::class)->create();
  	$payFacAccount = factory(\App\Models\Business\PayFacAccount::class)->create(['account_id' => $account->id]);
  	$this->assertInstanceOf('App\Models\Business\Account', $payFacAccount->account);
  }
}
