<?php

namespace Tests\Feature\Business;

use Tests\TestCase;
use App\Models\Business\AchBusiness;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AchBusinessTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function test_creating_a_pay_fac_business_creates_an_ach_business() {
    $this->assertEquals(0, count(AchBusiness::all()));
    factory(\App\Models\Business\AccountStatus::class)->create();
    $payFacBusiness = factory(\App\Models\Business\PayFacBusiness::class)->create();
    $this->assertEquals(1, count(AchBusiness::all()));
    $achBusiness = AchBusiness::where('ach_account_id', $payFacBusiness->payFacAccount->account->achAccount->id)->first();
    $this->assertEquals($payFacBusiness->business_name, $achBusiness->business_name);
    $this->assertNotEquals($payFacBusiness->payFacAccount->entity_type, $achBusiness->achAccount->business_type);
  }

  public function test_updating_pay_fac_business_updates_ach_business() {
    factory(\App\Models\Business\AccountStatus::class)->create();
    $oldAddress = $this->faker->streetAddress;
    $payFacBusiness = factory(\App\Models\Business\PayFacBusiness::class)->create(['address' => $oldAddress]);
    $achBusiness = AchBusiness::where('ach_account_id', $payFacBusiness->payFacAccount->account->achAccount->id)->first();
    $this->assertEquals($payFacBusiness->address, $achBusiness->address);
    $payFacBusiness->update(['address' => $this->faker->streetAddress]);

    $this->assertNotEquals($oldAddress, $payFacBusiness->fresh()->address);
    $this->assertNotEquals($oldAddress, $achBusiness->fresh()->address);
    $this->assertEquals($payFacBusiness->fresh()->address, $achBusiness->fresh()->address);
  }
}
