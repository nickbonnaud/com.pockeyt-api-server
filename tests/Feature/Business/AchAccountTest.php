<?php

namespace Tests\Feature\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Business\AchAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AchAccountTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_creating_pay_fac_account_creates_ach_account() {
    $this->assertEquals(0, count(AchAccount::all()));
    factory(\App\Models\Business\AccountStatus::class)->create();
    $payFacAccount = factory(\App\Models\Business\PayFacAccount::class)->create();
    $this->assertEquals(1, count(AchAccount::all()));
    $achAccount = AchAccount::where('account_id', $payFacAccount->account_id)->first();
    $this->assertNotNull($achAccount);
    $this->assertEquals($payFacAccount->account_id, $achAccount->account_id);
  }

  public function test_updating_pay_fac_account_updates_ach_account() {
    factory(\App\Models\Business\AccountStatus::class)->create();
    $businessType = 'soleProprietorship';
    $payFacAccount = factory(\App\Models\Business\PayFacAccount::class)->create(['entity_type' => $businessType]);
    $achAccount = AchAccount::where('account_id', $payFacAccount->account_id)->first();
    $this->assertEquals($businessType, $achAccount->business_type);
    $newBusinessType = 'llc';
    $payFacAccount->update(['entity_type' => $newBusinessType]);
    $this->assertNotEquals($businessType, $achAccount->fresh()->business_type);
    $this->assertEquals($newBusinessType, $achAccount->fresh()->business_type);
  }
}
