<?php

namespace Tests\Unit\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AchBusinessTest extends TestCase {
	use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

	public function test_an_ach_business_belongs_to_an_ach_account() {
  	factory(\App\Models\Business\AccountStatus::class)->create();
    $achAccount = factory(\App\Models\Business\AchAccount::class)->create();
  	$achBusiness = factory(\App\Models\Business\AchBusiness::class)->create(['ach_account_id' => $achAccount->id]);
  	$this->assertInstanceOf('App\Models\Business\AchBusiness', $achAccount->achBusiness);
  }

  public function test_an_ach_account_has_one_ach_business() {
  	factory(\App\Models\Business\AccountStatus::class)->create();
    $achAccount = factory(\App\Models\Business\AchAccount::class)->create();
  	$achBusiness = factory(\App\Models\Business\AchBusiness::class)->create(['ach_account_id' => $achAccount->id]);
  	$this->assertInstanceOf('App\Models\Business\AchAccount', $achBusiness->achAccount);
  }
}
