<?php

namespace Tests\Feature\Business;

use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unauth_business_cannot_request_customers() {
    $response = $this->send('', 'get', '/api/business/customers')->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_business_can_retrieve_active_customers() {
    $business = $this->createPosAccount();
    $activeCustomers = factory(\App\Models\Location\ActiveLocation::class, 12)->create(['location_id' => $business->location->id, 'customer_id' => factory(\App\Models\Customer\CustomerProfilePhoto::class)->create()->profile->customer_id]);

    $token = $this->createBusinessToken($business);

    $response = $this->send($token, 'get', '/api/business/customers?status=active')->getData();
    $this->assertEquals(count($activeCustomers), $response->meta->total);
  }

  public function test_a_business_can_retrieve_historic_customers() {
    $business = $this->createPosAccount();
    $historicCustomers = factory(\App\Models\Location\HistoricLocation::class, 8)->create(['location_id' => $business->location->id, 'customer_id' => factory(\App\Models\Customer\CustomerProfilePhoto::class)->create()->profile->customer_id]);

    $token = $this->createBusinessToken($business);

    $response = $this->send($token, 'get', '/api/business/customers?status=historic')->getData();
    $this->assertEquals(count($historicCustomers), $response->meta->total);
  }

  public function test_a_business_can_only_retrieve_their_customers() {
    $business = $this->createPosAccount();

    $activeCustomers = factory(\App\Models\Location\ActiveLocation::class, 9)->create(['location_id' => $business->location->id, 'customer_id' => factory(\App\Models\Customer\CustomerProfilePhoto::class)->create()->profile->customer_id]);

    $otherBusiness = $this->createPosAccount();
    factory(\App\Models\Location\ActiveLocation::class, 10)->create(['location_id' => $otherBusiness->location->id, 'customer_id' => factory(\App\Models\Customer\CustomerProfilePhoto::class)->create()->profile->customer_id]);

    $token = $this->createBusinessToken($business);

    $response = $this->send($token, 'get', '/api/business/customers?status=active')->getData();
    $this->assertEquals(count($activeCustomers), $response->meta->total);
  }

  public function test_a_business_retrieves_customers_ordered_by_most_recent() {
    $business = $this->createPosAccount();

    $activeCustomers = factory(\App\Models\Location\ActiveLocation::class, 12)->create(['location_id' => $business->location->id, 'customer_id' => factory(\App\Models\Customer\CustomerProfilePhoto::class)->create()->profile->customer_id, 'created_at' => Carbon::now()->subDays(rand(1, 100))]);

    $token = $this->createBusinessToken($business);

    $response = $this->send($token, 'get', '/api/business/customers?status=active')->getData();
    $storedActive = \App\Models\Location\ActiveLocation::orderBy('created_at', 'desc')->get();

    foreach ($storedActive as $key => $active) {
      $this->assertEquals($active->created_at->toDateTimeString(), $response->data[$key]->entered_at);
    }
  }

  public function test_a_business_can_retrieve_customers_active_with_transaction() {
    $business = $this->createPosAccount();
    $customer = factory(\App\Models\Customer\CustomerProfilePhoto::class)->create()->profile->customer;

    $activeLocations = factory(\App\Models\Location\ActiveLocation::class, 11)->create([
      'location_id' => $business->location->id,
      'customer_id' => $customer->id,
      'transaction_id' => factory(\App\Models\Transaction\Transaction::class)->create(['business_id' => $business->id, 'customer_id' => $customer->id])->id
    ]);

    factory(\App\Models\Location\ActiveLocation::class, 4)->create([
      'location_id' => $business->location->id,
      'customer_id' => $customer->id,
    ]);


    $token = $this->createBusinessToken($business);
    $response = $this->send($token, 'get', '/api/business/customers?status=active&withTransaction=true')->getData();

    $customersWithTransactionsCount = $business->location->activeCustomers()->whereNotNull('transaction_id')->count();
    $this->assertEquals($customersWithTransactionsCount, $response->meta->total);
    $this->assertEquals($activeLocations->count(), $response->meta->total);
  }

  public function test_a_business_can_retrieve_customers_historic_with_transaction() {
    $business = $this->createPosAccount();
    $customer = factory(\App\Models\Customer\CustomerProfilePhoto::class)->create()->profile->customer;

    $historicCustomers = factory(\App\Models\Location\HistoricLocation::class, 8)->create([
      'location_id' => $business->location->id,
      'customer_id' => $customer->id,
      'transaction_id' => factory(\App\Models\Transaction\Transaction::class)->create(['business_id' => $business->id, 'customer_id' => $customer->id])->id
    ]);

    factory(\App\Models\Location\HistoricLocation::class, 12)->create([
      'location_id' => $business->location->id,
      'customer_id' => $customer->id,
    ]);

    $token = $this->createBusinessToken($business);

    $response = $this->send($token, 'get', '/api/business/customers?status=historic&withTransaction=true')->getData();
    $customersWithTransactionsCount = $business->location->historicCustomers()->whereNotNull('transaction_id')->count();
    $this->assertEquals($customersWithTransactionsCount, $response->meta->total);
    $this->assertEquals($historicCustomers->count(), $response->meta->total);
  }

  public function test_a_business_can_retrieve_active_customers_without_transaction() {
    $business = $this->createPosAccount();
    $customer = factory(\App\Models\Customer\CustomerProfilePhoto::class)->create()->profile->customer;

    factory(\App\Models\Location\ActiveLocation::class, 11)->create([
      'location_id' => $business->location->id,
      'customer_id' => $customer->id,
      'transaction_id' => factory(\App\Models\Transaction\Transaction::class)->create(['business_id' => $business->id, 'customer_id' => $customer->id])->id
    ]);

    $activeLocations = factory(\App\Models\Location\ActiveLocation::class, 4)->create([
      'location_id' => $business->location->id,
      'customer_id' => $customer->id,
    ]);


    $token = $this->createBusinessToken($business);
    $response = $this->send($token, 'get', '/api/business/customers?status=active&withTransaction=false')->getData();
    $customersWithoutTransactionsCount = $business->location->activeCustomers()->whereNull('transaction_id')->count();
    $this->assertEquals($customersWithoutTransactionsCount, $response->meta->total);
    $this->assertEquals($activeLocations->count(), $response->meta->total);

  }

  public function test_a_business_can_retrieve_customers_historic_without_transaction() {
    $business = $this->createPosAccount();
    $customer = factory(\App\Models\Customer\CustomerProfilePhoto::class)->create()->profile->customer;

    factory(\App\Models\Location\HistoricLocation::class, 5)->create([
      'location_id' => $business->location->id,
      'customer_id' => $customer->id,
      'transaction_id' => factory(\App\Models\Transaction\Transaction::class)->create(['business_id' => $business->id, 'customer_id' => $customer->id])->id
    ]);

    $historicCustomers = factory(\App\Models\Location\HistoricLocation::class, 12)->create([
      'location_id' => $business->location->id,
      'customer_id' => $customer->id,
    ]);

    $token = $this->createBusinessToken($business);

    $response = $this->send($token, 'get', '/api/business/customers?status=historic&withTransaction=false')->getData();
    $customersWithoutTransactionsCount = $business->location->historicCustomers()->whereNull('transaction_id')->count();
    $this->assertEquals($customersWithoutTransactionsCount, $response->meta->total);
    $this->assertEquals($historicCustomers->count(), $response->meta->total);
  }

  public function test_a_business_can_retrieve_historic_with_date() {
    $business = $this->createPosAccount();
    $customer = factory(\App\Models\Customer\CustomerProfilePhoto::class)->create()->profile->customer;

    $startDate = urlencode(Carbon::now()->subDays(40)->toIso8601String());
    $endDate = urlencode(Carbon::now()->subDays(20)->toIso8601String());


    $historicCustomersWithTransaction = factory(\App\Models\Location\HistoricLocation::class, 5)->create([
      'location_id' => $business->location->id,
      'customer_id' => $customer->id,
      'transaction_id' => factory(\App\Models\Transaction\Transaction::class)->create(['business_id' => $business->id, 'customer_id' => $customer->id])->id,
      'created_at' => Carbon::now()->subDays(rand(21, 39))
    ]);

    $historicCustomersNoTransactions = factory(\App\Models\Location\HistoricLocation::class, 5)->create([
      'location_id' => $business->location->id,
      'customer_id' => $customer->id,
      'created_at' => Carbon::now()->subDays(rand(21, 39))
    ]);

    factory(\App\Models\Location\HistoricLocation::class, 5)->create([
      'location_id' => $business->location->id,
      'customer_id' => $customer->id,
      'transaction_id' => factory(\App\Models\Transaction\Transaction::class)->create(['business_id' => $business->id, 'customer_id' => $customer->id])->id,
      'created_at' => Carbon::now()->subDays(rand(1, 19))
    ]);

    factory(\App\Models\Location\HistoricLocation::class, 5)->create([
      'location_id' => $business->location->id,
      'customer_id' => $customer->id,
      'transaction_id' => factory(\App\Models\Transaction\Transaction::class)->create(['business_id' => $business->id, 'customer_id' => $customer->id])->id,
      'created_at' => Carbon::now()->subDays(rand(41, 67))
    ]);

    $token = $this->createBusinessToken($business);

    $response = $this->send($token, 'get', "/api/business/customers?status=historic&date[]={$startDate}&date[]={$endDate}")->getData();
    $this->assertEquals($historicCustomersWithTransaction->count() + $historicCustomersNoTransactions->count(), $response->meta->total);
  }

  // public function test_full_response_body() {
  //   $business = $this->createPosAccount();
  //   $customer = factory(\App\Models\Customer\CustomerProfilePhoto::class)->create()->profile->customer;
  //   $transaction = factory(\App\Models\Transaction\Transaction::class)->create(['business_id' => $business->id, 'customer_id' => $customer->id]);
  //   factory(\App\Models\Refund\Refund::class)->create(['transaction_id' => $transaction->id]);

  //   $activeLocations = factory(\App\Models\Location\ActiveLocation::class)->create([
  //     'location_id' => $business->location->id,
  //     'customer_id' => $customer->id,
  //     'transaction_id' => $transaction->id,
  //     'transaction_notification_id' => factory(\App\Models\Transaction\TransactionNotification::class)->create(['transaction_id' => $transaction->id])
  //   ]);

  //   $token = $this->createBusinessToken($business);
  //   $response = $this->send($token, 'get', '/api/business/customers?status=active&withTransaction=true')->getData();
  //   dd($response);
  // }


  private function createPosAccount() {
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'other']);
    factory(\App\Models\Business\Location::class)->create(['business_id' => $posAccount->business_id]);

    return $posAccount->business;
  }
}
