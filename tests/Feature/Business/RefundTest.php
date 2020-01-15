<?php

namespace Tests\Feature\Business;

use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RefundTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unauth_business_cannot_retrieve_refunds() {
    $response = $this->json('GET', '/api/business/refunds')->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_business_can_retrieve_refunds() {
    $refund = factory(\App\Models\Refund\Refund::class)->create(['transaction_id' => factory(\App\Models\Transaction\Transaction::class)->create(['customer_id' => $this->createCustomer()])->id]);
    $business = $refund->transaction->business;

    $numRefunds = 7;
    $i = 1;
    while ($i < $numRefunds) {
      $refunds = factory(\App\Models\Refund\Refund::class)->create(['transaction_id' => factory(\App\Models\Transaction\Transaction::class)->create(['business_id' => $business->id, 'customer_id' => $this->createCustomer()])->id]);
      $i++;
    }

    $this->businessHeaders($business);
    $response = $this->json('GET', '/api/business/refunds?recent=true')->getData();
    $this->assertEquals($numRefunds, $response->meta->total);
  }

  public function test_a_business_can_only_retrieve_their_refunds() {
    $refund = factory(\App\Models\Refund\Refund::class)->create(['transaction_id' => factory(\App\Models\Transaction\Transaction::class)->create(['customer_id' => $this->createCustomer()])->id]);
    $business = $refund->transaction->business;

    $refunds = factory(\App\Models\Refund\Refund::class, 14)->create(['transaction_id' => factory(\App\Models\Transaction\Transaction::class)->create(['business_id' => $business->id, 'customer_id' => $this->createCustomer()])->id]);
    $refunds = $refunds->push($refunds, $refund);

    factory(\App\Models\Refund\Refund::class, 10)->create();

    $this->businessHeaders($business);
    $response = $this->json('GET', '/api/business/refunds?recent=true')->getData();
    $this->assertEquals(count($refunds), $response->meta->total);
  }

  public function test_a_business_retrieves_refunds_ordered_by_most_recent() {
    $refund = factory(\App\Models\Refund\Refund::class)->create(['created_at' => Carbon::now()->subDays(rand(1, 100)), 'transaction_id' => factory(\App\Models\Transaction\Transaction::class)->create(['customer_id' => $this->createCustomer()])->id]);
    $business = $refund->transaction->business;

    $refunds = factory(\App\Models\Refund\Refund::class, 7)->create(['created_at' => Carbon::now()->subDays(rand(1, 100)), 'transaction_id' => factory(\App\Models\Transaction\Transaction::class)->create(['business_id' => $business->id, 'customer_id' => $this->createCustomer()])->id]);
    $refunds = $refunds->push($refunds, $refund);

    $this->businessHeaders($business);
    $response = $this->json('GET', '/api/business/refunds?recent=true')->getData();

    $storedRefunds = \App\Models\Refund\Refund::orderBy('created_at', 'desc')->get();
    
    foreach ($storedRefunds as $key => $refund) {
      $this->assertEquals($refund->created_at->toDateTimeString(), $response->data[$key]->refund->created_at);
    }
  }

  public function test_a_business_can_retrieve_refunds_by_date() {
    $startDate = urlencode(Carbon::now()->subDays(30)->toIso8601String());
    $endDate = urlencode(Carbon::now()->subDays(10)->toIso8601String());

    $refund = factory(\App\Models\Refund\Refund::class)->create(['created_at' => Carbon::now()->subDays(rand(11, 29)), 'transaction_id' => factory(\App\Models\Transaction\Transaction::class)->create(['customer_id' => $this->createCustomer()])->id]);
    $business = $refund->transaction->business;
    $refunds = factory(\App\Models\Refund\Refund::class, 14)->create(['created_at' => Carbon::now()->subDays(rand(11, 29)), 'transaction_id' => factory(\App\Models\Transaction\Transaction::class)->create(['business_id' => $business->id, 'customer_id' => $this->createCustomer()])->id]);

    factory(\App\Models\Refund\Refund::class, 14)->create(['created_at' => Carbon::now()->subDays(rand(0, 9)), 'transaction_id' => factory(\App\Models\Transaction\Transaction::class)->create(['business_id' => $business->id, 'customer_id' => $this->createCustomer()])->id]);

    factory(\App\Models\Refund\Refund::class, 14)->create(['created_at' => Carbon::now()->subDays(rand(31, 60)), 'transaction_id' => factory(\App\Models\Transaction\Transaction::class)->create(['business_id' => $business->id, 'customer_id' => $this->createCustomer()])->id]);

    $refunds = $refunds->push($refunds, $refund);

    $this->businessHeaders($business);
    $response = $this->json('GET', "/api/business/refunds?date[]={$startDate}&date[]={$endDate}")->getData();
    $this->assertEquals(count($refunds), $response->meta->total);
  }

  public function test_a_business_can_retrieve_refunds_by_customer_name() {
    $refund = factory(\App\Models\Refund\Refund::class)->create(['transaction_id' => factory(\App\Models\Transaction\Transaction::class)->create(['customer_id' => $this->createCustomer()])->id]);
    $business = $refund->transaction->business;
    $customer = $refund->transaction->customer;

    $refunds = factory(\App\Models\Refund\Refund::class, 9)->create(['transaction_id' => factory(\App\Models\Transaction\Transaction::class)->create(['business_id' => $business->id, 'customer_id' => $customer->id])->id]);
    $refunds = $refunds->push($refunds, $refund);

    $this->businessHeaders($business);
    $response = $this->json('GET', "/api/business/refunds?firstName={$customer->profile->first_name}&lastName={$customer->profile->last_name}")->getData();
    $this->assertEquals(count($refunds), $response->meta->total);
  }

  public function test_a_business_can_retrieve_a_refund_by_id() {
    $business = factory(\App\Models\Business\Business::class)->create();
    factory(\App\Models\Business\PosAccount::class)->create(['business_id' => $business->id]);
    $refund = factory(\App\Models\Refund\Refund::class)->create(['transaction_id' => factory(\App\Models\Transaction\Transaction::class)->create(['customer_id' => $this->createCustomer(), 'business_id' => $business->id])->id]);

    $this->businessHeaders($business);
    $response = $this->json('GET', "/api/business/refunds?id={$refund->identifier}")->getData();
    $this->assertEquals($refund->identifier, $response->data[0]->refund->identifier);
  }

  public function test_a_business_can_retrieve_a_refunds_by_transaction_id() {
    $transaction = factory(\App\Models\Transaction\Transaction::class)->create(['customer_id' => $this->createCustomer()]);
    $refund = factory(\App\Models\Refund\Refund::class)->create(['transaction_id' => $transaction->id]);

    $business = $transaction->business;
    $this->businessHeaders($business);
    $response = $this->json('GET', "/api/business/refunds?transactionId={$transaction->identifier}")->getData();
    $this->assertEquals($refund->identifier, $response->data[0]->refund->identifier);
    $this->assertEquals($transaction->identifier, $response->data[0]->transaction->identifier);
  }

  private function createCustomer() {
    return factory(\App\Models\Customer\CustomerProfilePhoto::class)->create()->profile->customer;
  }
}
