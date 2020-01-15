<?php

namespace Tests\Feature\Business;

use JWTAuth;
use Tests\TestCase;
use App\Models\Business\Business;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Helpers\TestHelpers;

class SquareAccountTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unauth_request_cannot_store_square_data() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $account = $business->account;

    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['business_id' => $business->id]);
    $code = 'temp_token';
    $state = JWTAuth::fromUser($account->business);

    $this->json('GET', "/api/business/pos/square/oauth?code={$code}")->assertRedirect(config('urls.dashboard.base') . '?oauth=fail');
  }

  public function test_an_auth_request_must_have_square_code() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $account = $business->account;

    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['business_id' => $business->id]);

    $code = 'temp_token';
    $state = JWTAuth::fromUser($account->business);

    $this->json('GET', "/api/business/pos/square/oauth?state={$state}")->assertRedirect(config('urls.dashboard.base') . '?oauth=fail');
  }

  public function test_a_request_must_have_correct_state_square_redirect() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $account = $business->account;

    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['business_id' => $business->id]);

    $code = 'temp_token';
    $state = JWTAuth::fromUser($account->business);

    $this->json('GET', "/api/business/pos/square/oauth?code={$code}&state=not_correct")->assertRedirect(config('urls.dashboard.base') . '?oauth=fail');
  }

  public function test_an_auth_request_can_store_square_data() {
    $account = $this->createRequiredAccounts();

    $code = 'temp_token';
    $state = JWTAuth::fromUser($account->business);

    $this->json('GET', "/api/business/pos/square/oauth?code={$code}&state={$state}")->assertRedirect(config('urls.dashboard.base') . '?oauth=success');

    $this->assertDatabaseHas('square_accounts', ['pos_account_id' => $account->id]);
  }

  public function test_storing_square_data_fetches_location_id_single_location() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $account = $business->account;

    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['business_id' => $business->id]);
    $payFacAccount = factory(\App\Models\Business\PayFacAccount::class)->create(['account_id' => $account->id]);
    $payFacBusinessAccount = factory(\App\Models\Business\PayFacBusiness::class)->create(['pay_fac_account_id' => $payFacAccount->id]);

    $code = 'temp_token';
    $state = JWTAuth::fromUser($account->business);

    $this->json('GET', "/api/business/pos/square/oauth?code={$code}&state={$state}")->assertRedirect(config('urls.dashboard.base') . '?oauth=success');
    
    $this->assertNotNull($posAccount->squareAccount->location_id);
    $this->assertEquals('18YC4JDH91E1H', $posAccount->squareAccount->location_id);
  }

  public function test_storing_square_data_fetches_location_id_multiple_locations() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $account = $business->account;

    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['business_id' => $business->id]);
    $payFacAccount = factory(\App\Models\Business\PayFacAccount::class)->create(['account_id' => $account->id]);
    $payFacBusinessAccount = factory(\App\Models\Business\PayFacBusiness::class)->create(['pay_fac_account_id' => $payFacAccount->id, 'address' => '321 Broad St']);
    
    $code = 'temp_token';
    $state = JWTAuth::fromUser($account->business);

    $this->json('GET', "/api/business/pos/square/oauth?code={$code}&state={$state}")->assertRedirect(config('urls.dashboard.base') . '?oauth=success');

    $this->assertNotNull($posAccount->squareAccount->location_id);
    $this->assertEquals('VVNJRU84HIG9BBV', $posAccount->squareAccount->location_id);
  }

  public function test_creating_square_account_fetches_and_stores_inventory() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $account = $business->account;

    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['business_id' => $business->id]);
    $payFacAccount = factory(\App\Models\Business\PayFacAccount::class)->create(['account_id' => $account->id]);
    $payFacBusinessAccount = factory(\App\Models\Business\PayFacBusiness::class)->create(['pay_fac_account_id' => $payFacAccount->id, 'address' => '321 Broad St']);
    
    $code = 'temp_token';
    $state = JWTAuth::fromUser($account->business);

    $this->json('GET', "/api/business/pos/square/oauth?code={$code}&state={$state}")->assertRedirect(config('urls.dashboard.base') . '?oauth=success');

    $this->assertDatabaseHas('active_items', ['inventory_id' => $account->business->inventory->id]);
    $this->assertNotNull($account->business->inventory->activeItems);
  }

  public function test_a_square_account_can_retrieve_an_employee() {
    $posAccount = $this->createRequiredAccounts();
    $squareAccount = factory(\App\Models\Business\SquareAccount::class)->create(['pos_account_id' => $posAccount->id]);
    $employeeData = $squareAccount->fetchEmployee("fake_id");
    $this->assertEquals("fake_id", $employeeData['id']);
  }

  private function createRequiredAccounts() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $account = $business->account;

    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['business_id' => $business->id]);
    $payFacAccount = factory(\App\Models\Business\PayFacAccount::class)->create(['account_id' => $account->id]);
    $payFacBusinessAccount = factory(\App\Models\Business\PayFacBusiness::class)->create(['pay_fac_account_id' => $payFacAccount->id]);
    factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'closed']);
    return $posAccount;
  }
}
