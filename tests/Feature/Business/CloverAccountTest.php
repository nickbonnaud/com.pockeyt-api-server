<?php

namespace Tests\Feature\Business;

use Tests\TestCase;
use App\Handlers\Http\HttpHandler;
use App\Helpers\CloverTestHelpers;
use App\Models\Business\Business;
use App\Models\Business\CloverAccount;
use App\Models\Transaction\UnassignedTransaction;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CloverAccountTest extends TestCase {
	use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

	public function test_an_unauth_request_cannot_store_clover_data() {
		$status = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id]);
    $code = 'bvjdb298bfui2b';
    $merchantId = 'mcvksay7fyerw8';

    $this->json('GET', "/api/business/pos/clover/oauth?merchant_id={$merchantId}&client_id=3t3782rgvcv&code={$code}")->assertRedirect(config('urls.dashboard.base') . '?oauth=fail');
	}

  public function test_an_auth_request_must_have_clover_code() {
    $status = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id]);
    $merchantId = 'mcvksay7fyerw8';

    $state = (Business::createToken($account->business))['token'];
    $this->json('GET', "/api/business/pos/clover/oauth?merchant_id={$merchantId}&client_id=3t3782rgvcv&state={$state}")->assertRedirect(config('urls.dashboard.base') . '?oauth=fail');
  }

  public function test_an_auth_request_must_have_clover_merchant_id() {
    $status = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id]);
    $code = 'bvjdb298bfui2b';
    $state = (Business::createToken($account->business))['token'];

    $this->json('GET', "/api/business/pos/clover/oauth?client_id=3t3782rgvcv&code={$code}&state={$state}")->assertRedirect(config('urls.dashboard.base') . '?oauth=fail');
  }

  public function test_a_request_must_have_correct_state_clover_redirect() {
    $status = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id]);
    $code = 'bvjdb298bfui2b';
    $merchantId = 'mcvksay7fyerw8';

    $this->json('GET', "/api/business/pos/clover/oauth?client_id=3t3782rgvcv&code={$code}&state=not_state&merchant_id={$merchantId}")->assertRedirect(config('urls.dashboard.base') . '?oauth=fail');
  }

	public function test_an_auth_request_can_store_clover_data() {
		$status = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id]);
    $code = 'bvjdb298bfui2b';
    $merchantId = 'mcvksay7fyerw8';

    $state = (Business::createToken($account->business))['token'];

    $this->json('GET', "/api/business/pos/clover/oauth?merchant_id={$merchantId}&client_id=3t3782rgvcv&code={$code}&state={$state}")->assertRedirect(config('urls.dashboard.base') . '?oauth=success');
    $this->assertDatabaseHas('clover_accounts', ['pos_account_id' => $account->id, 'merchant_id' => $merchantId]);
	}

	public function test_creating_clover_account_fetches_and_stores_inventory() {
		$status = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id]);
    $code = 'bvjdb298bfui2b';
    $merchantId = 'mcvksay7fyerw8';

    $state = (Business::createToken($account->business))['token'];

    $this->json('GET', "/api/business/pos/clover/oauth?merchant_id={$merchantId}&client_id=3t3782rgvcv&code={$code}&state={$state}")->assertRedirect(config('urls.dashboard.base') . '?oauth=success');
    $this->assertDatabaseHas('active_items', ['inventory_id' => $account->business->inventory->id]);
    $this->assertEquals(150, count($account->business->inventory->activeItems));
	}

  public function test_a_paid_transaction_closes_order_in_clover() {
    $status = factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'paid']);
    $transaction = factory(\App\Models\Transaction\Transaction::class)->create([
      'pos_transaction_id' => 'close_full_bill',
    ]);
    
    $transaction->status()->associate($status);
    $transaction->save();
    $this->assertEquals('paid', $transaction->fresh()->status->name);
  }

  public function test_a_clover_pos_can_assign_a_customer_to_an_order_keep_open() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    factory(\App\Models\Transaction\UnassignedTransaction::class)->create([
      'pos_transaction_id' => '12345',
    ]);
    $unassignedTransaction = factory(\App\Models\Transaction\UnassignedTransaction::class)->create([
      'pos_transaction_id' => '67890',
    ]);
     $this->assertEquals(2, UnassignedTransaction::count());

    $header = $this->businessHeaders($unassignedTransaction->business);
    $body = [
      'pos_transaction_id' => '67890',
      'customer_identifier' => $customer->identifier,
      'status_name' => 'open'
    ];

    $response = $this->json('PATCH', '/api/business/pos/clover/transaction', $body, $header)->getData();
    $this->assertDatabaseHas('transactions', ['pos_transaction_id' => '67890', 'customer_id' => $customer->id, 'locked' => true]);
    $this->assertEquals(1, UnassignedTransaction::count());
  }

  public function test_an_unauth_clover_business_cannot_assign_a_customer() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $unassignedTransaction = factory(\App\Models\Transaction\UnassignedTransaction::class)->create([
      'pos_transaction_id' => '67890',
    ]);

    $body = [
      'pos_transaction_id' => '67890',
      'customer_identifier' => $customer->identifier,
      'status_name' => 'open'
    ];

    $response = $this->json('PATCH', '/api/business/pos/clover/transaction', $body)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_a_clover_business_cannot_assign_a_customer_with_wrong_data() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $unassignedTransaction = factory(\App\Models\Transaction\UnassignedTransaction::class)->create([
      'pos_transaction_id' => '67890',
    ]);

    $header = $this->businessHeaders($unassignedTransaction->business);
    $body = [
      'pos_transaction_id' => '&^%$$#',
      'customer_identifier' => '12345',
      'status_name' => 'not_option'
    ];
    $response = $this->json('PATCH', '/api/business/pos/clover/transaction', $body, $header)->assertStatus(422);
    $response = $response->getData();
    $this->assertEquals("The pos transaction id may only contain letters, numbers, dashes and underscores.", $response->errors->pos_transaction_id[0]);
    $this->assertEquals("The selected customer identifier is invalid.", $response->errors->customer_identifier[0]);
    $this->assertEquals("The selected status name is invalid.", $response->errors->status_name[0]);
  }

  public function test_a_clover_account_can_retrieve_an_employee() {
    $cloverAccount = factory(\App\Models\Business\CloverAccount::class)->create();
    $employeeData = $cloverAccount->fetchEmployee('fake_id');
    $this->assertEquals("fake_id", $employeeData['id']);
  }
}
