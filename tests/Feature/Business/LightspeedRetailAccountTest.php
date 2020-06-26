<?php

namespace Tests\Feature\Business;

use JWTAuth;
use Tests\TestCase;
use App\Models\Business\Business;
use App\Models\Transaction\Transaction;
use App\Models\Transaction\PurchasedItem;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use App\Models\Business\LightspeedRetailAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Helpers\LightspeedRetailTestHelpers as TestHelpers;

class LightspeedRetailAccountTest extends TestCase {
  use WithFaker, RefreshDatabase;

   public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unauth_request_cannot_store_lightspeed_retail_data() {
    $status = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id]);
    $code = 'temp_token';
    $state = JWTAuth::fromUser($account->business);
    $response = $this->json('GET', "/api/business/pos/lsr/oauth?code={$code}")->assertRedirect(config('urls.dashboard.base') . '?oauth=fail');
  }

  public function test_an_auth_request_must_have_lightspeed_code() {
    $status = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id]);
    $code = 'temp_token';
    $state = JWTAuth::fromUser($account->business);
    $response = $this->json('GET', "/api/business/pos/lsr/oauth?state={$state}")->assertRedirect(config('urls.dashboard.base') . '?oauth=fail');
  }

  public function test_a_request_must_have_correct_token_value_for_state_lightspeed_retail() {
    $status = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id]);
    $code = 'temp_token';
    $token = JWTAuth::fromUser($account->business);
    $response = $this->json('GET', "/api/business/pos/lsr/oauth?code={$code}&state=12334")->assertRedirect(config('urls.dashboard.base') . '?oauth=fail');
  }

  public function test_an_auth_request_can_store_lightspeed_retail_data() {
    $status = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id]);
    $code = 'temp_token';
    $state = JWTAuth::fromUser($account->business);
    $this->json('GET', "/api/business/pos/lsr/oauth?code={$code}&state={$state}")->assertRedirect(config('urls.dashboard.base') . '?oauth=success');
    $this->assertDatabaseHas('lightspeed_retail_accounts', ['pos_account_id' => $account->id]);
  }

  public function test_creating_retail_account_fetches_account_id() {
    $status = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id]);
    $code = 'temp_token';
    $state = JWTAuth::fromUser($account->business);
    $this->json('GET', "/api/business/pos/lsr/oauth?code={$code}&state={$state}")->assertRedirect(config('urls.dashboard.base') . '?oauth=success');
    $this->assertDatabaseHas('lightspeed_retail_accounts', ['account_id' => 'not_real_account_id']);
  }

  public function test_creating_lightspeed_retail_account_creates_inventory() {
    $status = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id]);
    $code = 'temp_token';
    $state = JWTAuth::fromUser($account->business);
    $this->json('GET', "/api/business/pos/lsr/oauth?code={$code}&state={$state}")->assertRedirect(config('urls.dashboard.base') . '?oauth=success');
    $lightspeedAccount = LightspeedRetailAccount::first();
    $this->assertInstanceOf('App\Models\Business\Inventory', $lightspeedAccount->posAccount->business->inventory);
  }

  public function test_an_unauth_lightspeed_retail_pos_cannot_assign_a_customer_to_a_sale() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $lightspeedAccount = factory(\App\Models\Business\LightspeedRetailAccount::class)->create();
    $body = [
      'pos_transaction_id' => '89342guefwbihcbw',
      'customer_identifier' => $customer->identifier
    ];

    $response = $this->json('POST', '/api/business/pos/lsr/transaction', $body)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_a_lightspeed_retail_pos_can_assign_a_customer_to_a_sale() {
    Notification::fake();
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'lightspeed_retail']);
    $lightspeedAccount = factory(\App\Models\Business\LightspeedRetailAccount::class)->create(['pos_account_id' => $posAccount->id]);
    $headers = $this->businessHeaders($lightspeedAccount->posAccount->business);
    $body = [
      'pos_transaction_id' => 4401,
      'customer_identifier' => $customer->identifier
    ];

    $response = $this->json('POST', '/api/business/pos/lsr/transaction', $body, $headers)->getData();
    $this->assertEquals('Transaction created.', $response->success);
    $this->assertDatabaseHas('transactions', ['customer_id' => $customer->id, 'business_id' => $posAccount->business_id]);
  }

  public function test_a_lightspeed_pos_transaction_has_correct_tax_sub_and_total() {
    Notification::fake();
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'lightspeed_retail']);
    $lightspeedAccount = factory(\App\Models\Business\LightspeedRetailAccount::class)->create(['pos_account_id' => $posAccount->id]);
    $headers = $this->businessHeaders($lightspeedAccount->posAccount->business);
    $body = [
      'pos_transaction_id' => 4401,
      'customer_identifier' => $customer->identifier
    ];

    $response = $this->json('POST', '/api/business/pos/lsr/transaction', $body, $headers);

    $transaction = Transaction::first();
    $saleData = $lightspeedAccount->parseHttpResponse(TestHelpers::fakeLightspeedRetailSaleResponse())['Sale'];

    $this->assertEquals($saleData['totalDue'], $transaction->total);
    $this->assertEquals($saleData['calcTax1'] + $saleData['calcTax2'], $transaction->tax);
    $this->assertEquals($saleData['totalDue'] - $saleData['calcTax1'] - $saleData['calcTax2'], $transaction->net_sales);
  }

  public function test_a_lightspeed_pos_transaction_stores_purchased_items() {
    Notification::fake();
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'lightspeed_retail']);
    $lightspeedAccount = factory(\App\Models\Business\LightspeedRetailAccount::class)->create(['pos_account_id' => $posAccount->id]);
    $headers = $this->businessHeaders($lightspeedAccount->posAccount->business);
    $body = [
      'pos_transaction_id' => 4401,
      'customer_identifier' => $customer->identifier
    ];

    $response = $this->json('POST', '/api/business/pos/lsr/transaction', $body, $headers);
    $transaction = Transaction::first();
    $saleData = $lightspeedAccount->parseHttpResponse(TestHelpers::fakeLightspeedRetailSaleResponse())['Sale'];
    $this->assertEquals(count($saleData['SaleLines']['SaleLine']), count($transaction->purchasedItems));
  }

  public function test_a_lightspeed_transaction_with_employee_creates_employee_if_not_stored() {
    Notification::fake();;
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'lightspeed_retail']);
    $lightspeedAccount = factory(\App\Models\Business\LightspeedRetailAccount::class)->create(['pos_account_id' => $posAccount->id]);
    $headers = $this->businessHeaders($lightspeedAccount->posAccount->business);
    $body = [
      'pos_transaction_id' => 4401,
      'customer_identifier' => $customer->identifier
    ];

    $externalEmployeeId = $lightspeedAccount->parseHttpResponse(TestHelpers::fakeLightspeedRetailSaleResponse())['Sale']['employeeID'];
    $this->assertDatabaseMissing('employees', ['external_id' => $externalEmployeeId]);
    $response = $this->json('POST', '/api/business/pos/lsr/transaction', $body, $headers);

    $transaction = Transaction::first();
    $status = \App\Models\Transaction\TransactionStatus::where('name', 'paid')->first();
    $transaction->update(['status_id' => $status->id]);
    $this->assertDatabaseHas('employees', ['external_id' => $externalEmployeeId]);
  }

  public function test_a_lightspeed_transaction_with_employeedoes_not_create_employee_if_already_stored() {
    Notification::fake();;
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'lightspeed_retail']);
    $lightspeedAccount = factory(\App\Models\Business\LightspeedRetailAccount::class)->create(['pos_account_id' => $posAccount->id]);
    $headers = $this->businessHeaders($lightspeedAccount->posAccount->business);
    $body = [
      'pos_transaction_id' => 4401,
      'customer_identifier' => $customer->identifier
    ];

    $externalEmployeeId = $lightspeedAccount->parseHttpResponse(TestHelpers::fakeLightspeedRetailSaleResponse())['Sale']['employeeID'];
    factory(\App\Models\Business\Employee::class)->create(['external_id' => $externalEmployeeId, 'business_id' => $posAccount->business_id]);
    $this->assertEquals(1, \App\Models\Business\Employee::count());

    $response = $this->json('POST', '/api/business/pos/lsr/transaction', $body, $headers);

    $transaction = Transaction::first();
    $status = \App\Models\Transaction\TransactionStatus::where('name', 'paid')->first();
    $transaction->update(['status_id' => $status->id]);
    $this->assertEquals(1, \App\Models\Business\Employee::count());
  }

  public function test_a_lightspeed_access_token_is_refreshed_automatically_if_expired() {
    Notification::fake();
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'lightspeed_retail']);
    $expiry = time() - 30;
    $lightspeedAccount = factory(\App\Models\Business\LightspeedRetailAccount::class)->create(['pos_account_id' => $posAccount->id, 'expiry' => $expiry]);
    $lightspeedAccount->renewToken();
    $this->assertNotEquals($expiry, $lightspeedAccount->fresh()->expiry);
    $this->assertEquals('new_access_token', $lightspeedAccount->fresh()->access_token);
  }

  public function test_a_lightspeed_sale_can_be_refunded() {
    Notification::fake();
    factory(\App\Models\Refund\RefundStatus::class)->create(['name' => 'refund_pending']);
    $status = \App\Models\Transaction\TransactionStatus::where('name', 'paid')->first();
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'lightspeed_retail']);
    $lightspeedAccount = factory(\App\Models\Business\LightspeedRetailAccount::class)->create(['refresh_token' => 'refund', 'pos_account_id' => $posAccount->id]);
    
    $saleData = $lightspeedAccount->parseHttpResponse(TestHelpers::fakeSalePartialRefund())['Sale'];
    $saleLineData = $lightspeedAccount->parseHttpResponse(TestHelpers::fakeSalePartialRefundSaleLine())['SaleLine'];

    $transaction = factory(\App\Models\Transaction\Transaction::class)->create(['customer_id' => $customer->id, 'business_id' => $lightspeedAccount->posAccount->business_id, 'status_id' => $status->id, 'pos_transaction_id' => $saleLineData['saleID']]);
    
    $headers = $this->businessHeaders($lightspeedAccount->posAccount->business);
    $body = [
      'pos_transaction_id' => $saleData['saleID'],
      'customer_identifier' => $customer->identifier
    ];

    $response = $this->json('POST', '/api/business/pos/lsr/transaction', $body, $headers)->getData();
    $this->assertDatabaseHas('refunds', ['transaction_id' => $transaction->id, 'total' => abs($saleData['totalDue']), 'pos_refund_id' => $saleData['saleID']]);
  }

  public function test_a_lightspeed_retail_account_can_delete_transaction_if_not_paid_already() {
    Notification::fake();
    $status = \App\Models\Transaction\TransactionStatus::where('name', 'closed')->first();
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'lightspeed_retail']);
    $lightspeedAccount = factory(\App\Models\Business\LightspeedRetailAccount::class)->create(['refresh_token' => 'refund', 'pos_account_id' => $posAccount->id]);

    $saleData = $lightspeedAccount->parseHttpResponse(TestHelpers::fakeSalePartialRefund())['Sale'];
    $saleLineData = $lightspeedAccount->parseHttpResponse(TestHelpers::fakeSalePartialRefundSaleLine())['SaleLine'];

    $transaction = factory(\App\Models\Transaction\Transaction::class)->create(['customer_id' => $customer->id, 'business_id' => $lightspeedAccount->posAccount->business_id, 'status_id' => $status->id, 'pos_transaction_id' => $saleLineData['saleID']]);
    
    $headers = $this->businessHeaders($lightspeedAccount->posAccount->business);
    $body = [
      'pos_transaction_id' => $saleData['saleID'],
      'customer_identifier' => $customer->identifier
    ];

    $response = $this->json('POST', '/api/business/pos/lsr/transaction', $body, $headers)->getData();
    $this->assertDatabaseMissing('refunds', ['transaction_id' => $transaction->id, 'total' => abs($saleData['totalDue']), 'pos_refund_id' => $saleData['saleID']]);
    $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
    $this->assertEquals(0, Transaction::count());
    $this->assertEquals(0, PurchasedItem::count());
  }


  // public function test_get_sale_data() {
  //   $lightspeedAccount = factory(\App\Models\Business\LightspeedRetailAccount::class)->create(['access_token' => env('LIGHTSPEED_TEST_ACCESS_TOKEN'), 'refresh_token' => env('LIGHTSPEED_TEST_REFRESH_TOKEN'), 'account_id' => env('LIGHTSPEED_TEST_ACCOUNT_ID')]);
  //   $response = $lightspeedAccount->getSaleData(23);
  //   dd($response);
  // }


  // public function test_renew_access_token() {
  //   $lightspeedAccount = factory(\App\Models\Business\LightspeedRetailAccount::class)->create(['access_token' => env('LIGHTSPEED_TEST_ACCESS_TOKEN'), 'refresh_token' => env('LIGHTSPEED_TEST_REFRESH_TOKEN'), 'account_id' => env('LIGHTSPEED_TEST_ACCOUNT_ID')]);

  //   $response = $lightspeedAccount->refreshToken();
  //   dd($response);
  // }
}
