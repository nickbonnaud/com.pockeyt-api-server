<?php

namespace Tests\Feature\Business;

use Tests\TestCase;
use App\Models\Business\AchOwner;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AchOwnerTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_creating_a_pay_fac_owner_creates_an_ach_owner() {
    $this->assertEquals(0, count(AchOwner::all()));
    factory(\App\Models\Business\AccountStatus::class)->create();
    $payFacOwner = factory(\App\Models\Business\PayFacOwner::class)->create();
    $this->assertEquals(1, count(AchOwner::all()));
    $achOwner = AchOwner::where('ach_account_id', $payFacOwner->payFacAccount->account->achAccount->id)->first();
    $this->assertEquals($payFacOwner->last_name, $achOwner->last_name);
    $this->assertDatabaseHas('ach_owners', ['last_name' => $payFacOwner->last_name]);
  }
  
  public function test_updating_pay_fac_owner_updates_ach_owner() {
    factory(\App\Models\Business\AccountStatus::class)->create();
    $oldName = $this->faker->lastName;
    $payFacOwner = factory(\App\Models\Business\PayFacOwner::class)->create(['last_name' => $oldName]);
    $achOwner = AchOwner::where('ach_account_id', $payFacOwner->payFacAccount->account->achAccount->id)->first();
    $this->assertEquals($payFacOwner->last_name, $achOwner->last_name);
    $payFacOwner->update(['last_name' => $this->faker->streetAddress]);

    $this->assertNotEquals($oldName, $payFacOwner->fresh()->last_name);
    $this->assertNotEquals($oldName, $achOwner->fresh()->last_name);
    $this->assertEquals($payFacOwner->fresh()->last_name, $achOwner->fresh()->last_name);
  }
}
