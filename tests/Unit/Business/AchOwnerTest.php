<?php

namespace Tests\Unit\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AchOwnerTest extends TestCase {
	use WithFaker, RefreshDatabase;

	public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

	public function test_an_ach_owner_belongs_to_an_ach_account() {
		factory(\App\Models\Business\AccountStatus::class)->create();
    $achAccount = factory(\App\Models\Business\AchAccount::class)->create();
    $achOwner = factory(\App\Models\Business\AchOwner::class)->create(['ach_account_id' => $achAccount->id]);
    $this->assertInstanceOf('App\Models\Business\AchOwner', $achAccount->achOwners->first());
	}

	public function test_an_ach_account_has_many_ach_owners(){
		factory(\App\Models\Business\AccountStatus::class)->create();
    $achAccount = factory(\App\Models\Business\AchAccount::class)->create();
    $achOwner = factory(\App\Models\Business\AchOwner::class)->create(['ach_account_id' => $achAccount->id]);
    $this->assertInstanceOf('App\Models\Business\AchAccount', $achOwner->achAccount);
	}

	public function test_an_ach_account_can_have_multiple_owners() {
		factory(\App\Models\Business\AccountStatus::class)->create();
    $achAccount = factory(\App\Models\Business\AchAccount::class)->create();
    factory(\App\Models\Business\AchOwner::class, 2)->create(['ach_account_id' => $achAccount->id]);

    $this->assertEquals(2, $achAccount->achOwners->count());
	}

	public function test_ach_owner_ssn_is_automatically_encrypted() {
		factory(\App\Models\Business\AccountStatus::class)->create();
		$ssn = $this->faker->ssn;
		$owner = factory(\App\Models\Business\AchOwner::class)->create(['ssn' => $ssn]);
		$this->assertDatabaseHas('ach_owners', ['first_name' => $owner->first_name, 'last_name' => $owner->last_name]);
		$this->assertDatabaseMissing('ach_owners', ['first_name' => $owner->first_name, 'last_name' => $owner->last_name, 'ssn' => $ssn]);
	}

	public function test_ach_owner_ssn_is_automatically_decrypted_when_accessed() {
		factory(\App\Models\Business\AccountStatus::class)->create();
		$ssn = $this->faker->ssn;
		$owner = factory(\App\Models\Business\AchOwner::class)->create(['ssn' => $ssn]);
		$this->assertSame($ssn, $owner->ssn);
		$this->assertNotEquals($ssn, $owner->getRawOriginal('ssn'));
	}

}
