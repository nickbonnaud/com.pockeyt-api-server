<?php

namespace Tests\Feature\Customer;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;
use App\Models\Transaction\TransactionStatus;

class TransactionTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unauth_customer_cannot_fetch_transactions() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Transaction\Transaction::class, 12)->create(['customer_id' => $customer->id]);

    $response = $this->json('GET', 'api/customer/transaction')->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_customer_can_fetch_transactions_default() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $numTransactions = 3;
    $i = 1;
    while ($i <= $numTransactions) {
      $this->createTransaction($customer);
      $i++;
    }
    $headers = $this->customerHeaders($customer);

    $response = $this->json('GET', 'api/customer/transaction')->getData();
    $this->assertEquals($numTransactions, count($response->data));
  }

  public function test_an_auth_customer_can_only_fetch_their_transactions() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $numTransactions = 10;
    $i = 1;
    while ($i <= $numTransactions) {
      $this->createTransaction($customer);
      $i++;
    }

    $x = 1;
    while ($x <= 5) {
      $this->createTransaction(factory(\App\Models\Customer\Customer::class)->create());
      $x++;
    }
    $headers = $this->customerHeaders($customer);

    $response = $this->json('GET', 'api/customer/transaction')->getData();
    $this->assertEquals($numTransactions, count($response->data));
  }

  public function test_an_auth_customer_can_request_transactions_by_status() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $numPaidTransactions = 12;
    $i = 1;

    $statusId = \App\Models\Transaction\TransactionStatus::where('name', 'paid')->first()->id;
    while ($i <= $numPaidTransactions) {
      $this->createTransaction($customer, $statusId);
      $i++;
    }

    $numOpenTransactions = 6;
    $x = 1;
    while ($x <= $numOpenTransactions) {
      $this->createTransaction($customer, 1);
      $x++;
    }
    $headers = $this->customerHeaders($customer);

    $response = $this->json('GET', 'api/customer/transaction?status=200')->getData();
    $this->assertEquals($numPaidTransactions, $response->meta->total);
    $response = $this->json('GET', 'api/customer/transaction')->getData();
    $this->assertEquals($numPaidTransactions + $numOpenTransactions, $response->meta->total);
  }

  public function test_an_auth_customer_can_request_transactions_by_business() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $numTransactionsByBusiness = 7;

    $business = $this->createTransaction($customer, 0, $numTransactionsByBusiness);

    $numOpenTransactions = 6;
    $x = 1;
    while ($x <= $numOpenTransactions) {
      $this->createTransaction($customer);
      $x++;
    }
    $headers = $this->customerHeaders($customer);

    $response = $this->json('GET', "api/customer/transaction?business={$business->identifier}")->getData();
    $this->assertEquals($numTransactionsByBusiness, $response->meta->total);
    $response = $this->json('GET', 'api/customer/transaction')->getData();
    $this->assertEquals($numTransactionsByBusiness + $numOpenTransactions, $response->meta->total);
  }

  public function test_an_auth_customer_can_request_transaction_by_identifier() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();

    $this->createTransaction($customer);
    $transaction = \App\Models\Transaction\Transaction::first();

    $numOtherTransactions = 6;
    $x = 1;
    while ($x <= $numOtherTransactions) {
      $this->createTransaction($customer);
      $x++;
    }
    $headers = $this->customerHeaders($customer);

    $response = $this->json('GET', "api/customer/transaction?id={$transaction->identifier}")->getData();
    $this->assertEquals($transaction->identifier, $response->data[0]->transaction->identifier);
    $this->assertEquals(1, $response->meta->total);
  }

  public function test_a_customer_can_fetch_transactions_by_date() {
    $startDate = urlencode(Carbon::now()->subDays(5)->toIso8601String());
    $endDate = urlencode(Carbon::now()->subDays(2)->toIso8601String());

    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $numTransactions = 7;
    $i = 1;
    while ($i <= $numTransactions) {
      $this->createTransaction($customer);
      $i++;
    }
    $headers = $this->customerHeaders($customer);

    $transactionInDate = \App\Models\Transaction\Transaction::first();
    $transactionInDate->update(['created_at' => Carbon::now()->subDays(3)]);

    $response = $this->json('GET', "api/customer/transaction?date[]={$startDate}&date[]={$endDate}")->getData();
    $this->assertEquals(1, count($response->data));
    $this->assertEquals($transactionInDate->identifier, $response->data[0]->transaction->identifier);
  }

  public function test_an_auth_customer_can_fetch_their_open_transactions() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $numPaidTransactions = 15;
    $i = 1;

    $statusId = \App\Models\Transaction\TransactionStatus::where('name', 'paid')->first()->id;
    while ($i <= $numPaidTransactions) {
      $this->createTransaction($customer, $statusId);
      $i++;
    }

    $numOpenTransactions = 3;
    $x = 1;
    while ($x <= $numOpenTransactions) {
      $this->createTransaction($customer, 1);
      $x++;
    }
    $headers = $this->customerHeaders($customer);

    $response = $this->json('GET', "api/customer/transaction?open=true")->getData();
    $this->assertEquals($numOpenTransactions, $response->meta->total);
  }

  public function test_an_unauth_customer_cannot_approve_transaction() {
    Notification::fake();
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $statusId = \App\Models\Transaction\TransactionStatus::where('code', 101)->first()->id;
    $business = $this->createTransaction($customer, $statusId);
    $transaction = $business->transactions()->first();

    $body = [
      'status_code' => 104
    ];

    $response = $this->json('PATCH', "api/customer/transaction/{$transaction->identifier}", $body)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_customer_can_only_approve_a_transaction_they_own() {
    Notification::fake();
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $statusId = \App\Models\Transaction\TransactionStatus::where('code', 101)->first()->id;
    $this->createTransaction($customer, $statusId);
    $business = $this->createTransaction(factory(\App\Models\Customer\Customer::class)->create(), $statusId);
    $transaction = $business->transactions()->first();

    $this->customerHeaders($customer);

    $body = [
      'status_code' => 104
    ];

    $response = $this->json('PATCH', "api/customer/transaction/{$transaction->identifier}", $body)->assertStatus(403);
    $this->assertEquals('Permission denied.', $response->getData()->errors);
  }

  public function test_an_auth_customer_can_only_update_transaction_with_correct_data() {
    Notification::fake();
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $statusId = \App\Models\Transaction\TransactionStatus::where('code', 101)->first()->id;
    $this->createTransaction($customer, $statusId);
    $business = $this->createTransaction(factory(\App\Models\Customer\Customer::class)->create(), $statusId);
    $transaction = $business->transactions()->first();

    $this->customerHeaders($customer);

    $body = [
      'status_code' => 'not code'
    ];

    $response = $this->json('PATCH', "api/customer/transaction/{$transaction->identifier}", $body)->assertStatus(422);
    $this->assertEquals('The status code must be an integer.', $response->getData()->errors->status_code[0]);

    $body = [
      'status_code' => 800
    ];

    $response = $this->json('PATCH', "api/customer/transaction/{$transaction->identifier}", $body)->assertStatus(422);
    $this->assertEquals('The selected status code is invalid.', $response->getData()->errors->status_code[0]);
  }

  public function test_an_auth_customer_can_approve_their_transaction() {
    Notification::fake();
    $customer = $this->createCustomer();
    $statusId = \App\Models\Transaction\TransactionStatus::where('code', 101)->first()->id;
    $business = $this->createTransaction($customer, $statusId);
    $transaction = $business->transactions()->first();

    $this->customerHeaders($customer);
    $body = [
      'status_code' => 104
    ];

    $response = $this->json('PATCH', "api/customer/transaction/{$transaction->identifier}", $body)->getData();
    $this->assertEquals($response->data->transaction->status->code, 103);
    $this->assertDatabaseHas('transactions', ['customer_id' => $customer->id, 'business_id' => $business->id, 'status_id' => \App\Models\Transaction\TransactionStatus::where('code', 103)->first()->id]);
  }
  

  private function createCustomer() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\AchCustomer::class)->create(['customer_account_id' => $customer->account->id]);
    return $customer;
  }

  private function createTransaction($customer, $statusId = null, $numTransactions = 1) {
    $profilePhotos = factory(\App\Models\Business\ProfilePhotos::class)->create();
    factory(\App\Models\Business\PosAccount::class)->create(['business_id' => $profilePhotos->profile->business_id]);
    $location = factory(\App\Models\Business\Location::class)->create(['business_id' => $profilePhotos->profile->business_id]);
    factory(\App\Models\Business\GeoAccount::class)->create(['location_id' => $location->id]);
    factory(\App\Models\Business\BeaconAccount::class)->create(['location_id' => $location->id]);
    factory(\App\Models\Business\AchAccount::class)->create(['account_id' => $profilePhotos->profile->business->account->id]);

    $statusId = $statusId == null ? \App\Models\Transaction\TransactionStatus::where('name', 'paid')->first()->id : $statusId;
    $transactions = factory(\App\Models\Transaction\Transaction::class, $numTransactions)->create(['customer_id' => $customer->id, 'business_id' => $profilePhotos->profile->business_id, 'status_id' => $statusId]);
    foreach ($transactions as $transaction) {
      factory(\App\Models\Transaction\PurchasedItem::class, 2)->create(['transaction_id' => $transaction->id]);
      factory(\App\Models\Transaction\PurchasedItem::class, 2)->create(['transaction_id' => $transaction->id, 'item_id' => \App\Models\Business\ActiveItem::first()->id]);
    }
    return $profilePhotos->profile->business;
  }
}
