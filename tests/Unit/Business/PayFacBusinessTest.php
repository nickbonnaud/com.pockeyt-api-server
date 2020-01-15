<?php

namespace Tests\Unit\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PayFacBusinessTest extends TestCase {
	use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }
  
  public function test_a_pay_fac_business_creates_a_unique_identifier() {
    factory(\App\Models\Business\AccountStatus::class)->create();
    $payFacBusiness = factory(\App\Models\Business\PayFacBusiness::class)->create();
    $this->assertNotNull($payFacBusiness->identifier);
  }

  public function test_a_pay_fac_business_belongs_to_a_pay_fac_account() {
  	factory(\App\Models\Business\AccountStatus::class)->create();
    $payFacAccount = factory(\App\Models\Business\PayFacAccount::class)->create();
  	$payFacBusiness = factory(\App\Models\Business\PayFacBusiness::class)->create(['pay_fac_account_id' => $payFacAccount->id]);
  	$this->assertInstanceOf('App\Models\Business\PayFacBusiness', $payFacAccount->payFacBusiness);
  }

  public function test_a_pay_fac_account_has_one_pay_fac_business() {
  	factory(\App\Models\Business\AccountStatus::class)->create();
    $payFacAccount = factory(\App\Models\Business\PayFacAccount::class)->create();
  	$payFacBusiness = factory(\App\Models\Business\PayFacBusiness::class)->create(['pay_fac_account_id' => $payFacAccount->id]);
  	$this->assertInstanceOf('App\Models\Business\PayFacAccount', $payFacBusiness->payFacAccount);
  }

  public function test_creating_a_payfac_business_account_sets_account_status_to_103() {
    $payFacBusiness = factory(\App\Models\Business\PayFacBusiness::class)->create();
    $this->assertEquals(103, $payFacBusiness->payfacAccount->account->status->code);
  }
}
