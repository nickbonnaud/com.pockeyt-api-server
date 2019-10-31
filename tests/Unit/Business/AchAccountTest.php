<?php

namespace Tests\Unit\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AchAccountTest extends TestCase {
	use WithFaker, RefreshDatabase;

	public function test_an_ach_acount_creates_a_unique_identifier() {
		factory(\App\Models\Business\AccountStatus::class)->create();
		$achAccount = factory(\App\Models\Business\AchAccount::class)->create();
		$this->assertNotNull($achAccount->identifier);
	}

	public function test_an_ach_account_belongs_to_an_account() {
		factory(\App\Models\Business\AccountStatus::class)->create();
		$account = factory(\App\Models\Business\Account::class)->create();
		$achAccount = factory(\App\Models\Business\AchAccount::class)->create(['account_id' => $account->id]);
		$this->assertInstanceOf('App\Models\Business\AchAccount', $account->achAccount);
	}

	public function test_an_account_has_one_ach_account() {
		factory(\App\Models\Business\AccountStatus::class)->create();
		$account = factory(\App\Models\Business\Account::class)->create();
		$achAccount = factory(\App\Models\Business\AchAccount::class)->create(['account_id' => $account->id]);
		$this->assertInstanceOf('App\Models\Business\Account', $achAccount->account);
	}
}
