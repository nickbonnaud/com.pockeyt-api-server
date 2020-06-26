<?php

namespace Tests\Feature\Customer;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;

class RefundTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unauth_customer_cannot_fetch_refunds() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $numRefunds = 5;
    $i = 1;
    while ($i <= $numRefunds) {
      $this->createRefunds($customer);
      $i++;
    }
    $response = $response = $this->json('GET', 'api/customer/refund')->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_customer_can_fetch_refunds_default_query() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $numRefunds = 5;
    $i = 1;
    while ($i <= $numRefunds) {
      $this->createRefunds($customer);
      $i++;
    }
    $this->customerHeaders($customer);

    $response = $response = $this->json('GET', 'api/customer/refund')->getData();
    $this->assertEquals($numRefunds, count($response->data));
  }

  public function test_a_customer_can_only_fetch_their_refunds() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $numRefunds = 7;
    $i = 1;
    while ($i <= $numRefunds) {
      $this->createRefunds($customer);
      $i++;
    }
    $this->customerHeaders($customer);

    $x = 1;
    while ($x <= 4) {
      $this->createRefunds(factory(\App\Models\Customer\Customer::class)->create());
      $x++;
    }

    $response = $response = $this->json('GET', 'api/customer/refund')->getData();
    $this->assertEquals($numRefunds, count($response->data));
    $this->assertEquals($numRefunds + 4, \App\Models\Refund\Refund::count());
  }

  public function test_a_customer_can_fetch_refunds_by_status() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $statusId = \App\Models\Refund\RefundStatus::where('code', 100)->first()->id;
    $numPendingRefunds = 6;
    $i = 1;
    while ($i <= $numPendingRefunds) {
      $this->createRefunds($customer, $statusId);
      $i++;
    }
    $this->customerHeaders($customer);

    $x = 1;
    while ($x <= 2) {
      $this->createRefunds($customer);
      $x++;
    }

    $response = $response = $this->json('GET', 'api/customer/refund?status=100')->getData();
    $this->assertEquals($numPendingRefunds, count($response->data));
     $this->assertEquals($numPendingRefunds + 2, \App\Models\Refund\Refund::count());
  }

  public function test_a_customer_can_fetch_refunds_by_business() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $numRefundsByBusiness = 3;
    $business =  $this->createRefunds($customer, 2, $numRefundsByBusiness);

    $i = 1;
    while ($i <= 5) {
      $this->createRefunds($customer);
      $i++;
    }
    $this->customerHeaders($customer);

    $response = $response = $this->json('GET', "api/customer/refund?business={$business->identifier}")->getData();
    $this->assertEquals($numRefundsByBusiness, $response->meta->total);
    $this->assertEquals($numRefundsByBusiness + 5, \App\Models\Refund\Refund::count());
  }

  public function test_a_customer_can_fetch_refund_by_identifier() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $this->createRefunds($customer);
    $refund = \App\Models\Refund\Refund::first();

    $numRefunds = 12;
    $i = 1;
    while ($i <= $numRefunds) {
      $this->createRefunds($customer);
      $i++;
    }
    $this->customerHeaders($customer);

    $response = $response = $this->json('GET', "api/customer/refund?id={$refund->identifier}")->getData();
    $this->assertEquals($refund->identifier, $response->data[0]->refund->identifier);
    $this->assertEquals(1, $response->meta->total);
  }

  public function test_a_customer_can_fetch_refund_by_transaction_id() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $this->createRefunds($customer);
    $transaction = \App\Models\Refund\Refund::first()->transaction;

    $numRefunds = 12;
    $i = 1;
    while ($i <= $numRefunds) {
      $this->createRefunds($customer);
      $i++;
    }
    $this->customerHeaders($customer);

    $response = $response = $this->json('GET', "api/customer/refund?transactionId={$transaction->identifier}")->getData();
    $this->assertEquals($transaction->identifier, $response->data[0]->transaction->identifier);
    $this->assertEquals(1, $response->meta->total);
  }

  public function test_a_customer_can_fetch_refund_by_date() {
    $startDate = urlencode(Carbon::now()->subDays(5)->toIso8601String());
    $endDate = urlencode(Carbon::now()->subDays(2)->toIso8601String());

    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $numRefunds = 2;
    $i = 1;
    while ($i <= $numRefunds) {
      $this->createRefunds($customer);
      $i++;
    }
    $this->customerHeaders($customer);

    $refundInDate = \App\Models\Refund\Refund::first();
    $refundInDate['created_at'] = Carbon::now()->subDays(3);
    $refundInDate->save();

    $response = $this->json('GET', "api/customer/refund?date[]={$startDate}&date[]={$endDate}")->getData();
    $this->assertEquals(1, count($response->data));
    $this->assertEquals($refundInDate->identifier, $response->data[0]->refund->identifier);
  }



  private function createRefunds($customer, $statusId = 2, $numRefunds = 1) {
    $profilePhotos = factory(\App\Models\Business\ProfilePhotos::class)->create();
    factory(\App\Models\Business\PosAccount::class)->create(['business_id' => $profilePhotos->profile->business_id]);
    $location = factory(\App\Models\Business\Location::class)->create(['business_id' => $profilePhotos->profile->business_id]);
    factory(\App\Models\Business\GeoAccount::class)->create(['location_id' => $location->id]);
    factory(\App\Models\Business\BeaconAccount::class)->create(['location_id' => $location->id]);

    $transaction = factory(\App\Models\Transaction\Transaction::class)->create(['customer_id' => $customer->id, 'business_id' => $profilePhotos->profile->business_id]);
    factory(\App\Models\Transaction\PurchasedItem::class, 2)->create(['transaction_id' => $transaction->id]);
    factory(\App\Models\Transaction\PurchasedItem::class, 2)->create(['transaction_id' => $transaction->id, 'item_id' => \App\Models\Business\ActiveItem::first()->id]);
    factory(\App\Models\Refund\Refund::class, $numRefunds)->create(['transaction_id' => $transaction->id, 'status_id' => $statusId]);
    return $profilePhotos->profile->business;
  }
}
