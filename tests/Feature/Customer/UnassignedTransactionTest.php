<?php

namespace Tests\Feature\Customer;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UnassignedTransactionTest extends TestCase {
  
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unauth_customer_cannot_fetch_unassignedTransactions() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $business = factory(\App\Models\Business\Business::class)->create();

    factory(\App\Models\Transaction\UnassignedTransaction::class, 12)->create(['business_id' => $business->id]);

    $response = $this->json('GET', "api/customer/unassigned-transaction?business_id={$business->identifier}")->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_a_customer_must_provide_correct_business_identifier_to_fetch_unassigned_transactions() {
    $numTransactions = 13;
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $business = $this->createTransaction($numTransactions);

    $headers = $this->customerHeaders($customer);
    $response = $this->json('GET', "api/customer/unassigned-transaction?business_id=fake")->assertStatus(422);
    $this->assertEquals('The selected business id is invalid.', ($response->getData())->errors->business_id[0]);
  }

  public function test_a_customer_can_fetch_unassigned_transactions() {
    $numTransactions = 12;
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $business = $this->createTransaction($numTransactions);

    $headers = $this->customerHeaders($customer);
    $response = $this->json('GET', "api/customer/unassigned-transaction?business_id={$business->identifier}")->getData();

    $this->assertEquals($numTransactions, count($response->data));
  }

  public function test_a_customer_is_only_returned_unassigned_transactions_from_single_business() {
    $numTransactions = 7;
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $business = $this->createTransaction($numTransactions);

    $this->createTransaction(5);

    $headers = $this->customerHeaders($customer);
    $response = $this->json('GET', "api/customer/unassigned-transaction?business_id={$business->identifier}")->getData();

    $this->assertEquals($numTransactions, count($response->data));
  }

  public function test_an_unauth_customer_cannot_claim_an_unassigned_transaction() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $business = $this->createTransaction();
    $transaction = \App\Models\Transaction\UnassignedTransaction::first();

    $response = $this->json('PATCH', "api/customer/unassigned-transaction/{$transaction->identifier}")->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_customer_can_claim_an_unassigned_transaction() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $business = $this->createTransaction();
    $transaction = \App\Models\Transaction\UnassignedTransaction::first();

    $this->customerHeaders($customer);

    $response = $this->json('PATCH', "api/customer/unassigned-transaction/{$transaction->identifier}")->getData();
    $this->assertDatabaseHas('transactions', ['bill_created_at' => $transaction->created_at]);
    $this->assertEquals($transaction->created_at, $response->data->transaction->bill_created_at);
  }


  private function createTransaction($numTransactions = 1) {
    $profilePhotos = factory(\App\Models\Business\ProfilePhotos::class)->create();
    factory(\App\Models\Business\PosAccount::class)->create(['business_id' => $profilePhotos->profile->business_id]);
    $location = factory(\App\Models\Business\Location::class)->create(['business_id' => $profilePhotos->profile->business_id]);
    factory(\App\Models\Business\GeoAccount::class)->create(['location_id' => $location->id]);
    factory(\App\Models\Business\BeaconAccount::class)->create(['location_id' => $location->id]);

    $transactions = factory(\App\Models\Transaction\UnassignedTransaction::class, $numTransactions)->create(['business_id' => $profilePhotos->profile->business_id]);

    foreach ($transactions as $transaction) {
      factory(\App\Models\Transaction\UnassignedPurchasedItem::class, 2)->create(['unassigned_transaction_id' => $transaction->id]);
      factory(\App\Models\Transaction\UnassignedPurchasedItem::class, 2)->create(['unassigned_transaction_id' => $transaction->id, 'item_id' => \App\Models\Business\ActiveItem::first()->id]);
    }

    return $profilePhotos->profile->business;
  }
}
