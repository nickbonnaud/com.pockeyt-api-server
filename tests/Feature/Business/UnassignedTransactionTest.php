<?php

namespace Tests\Feature\Business;

use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UnassignedTransactionTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unauth_business_cannot_request_unassigned() {
    $numTrans = 11;
    $unassigned = factory(\App\Models\Transaction\UnassignedTransaction::class, $numTrans)->create();
    $response = $this->send("", 'get', '/api/business/unassigned-transactions?recent=true')->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_a_business_can_request_unassigned_recent() {
    $employee = factory(\App\Models\Business\Employee::class)->create();
    $inventory = factory(\App\Models\Business\Inventory::class)->create(['business_id' => $employee->business_id]);
    $activeItem = factory(\App\Models\Business\ActiveItem::class)->create(['inventory_id' => $inventory->id]);

    $numTrans = 6;
    $unassigned = factory(\App\Models\Transaction\UnassignedTransaction::class, $numTrans)
      ->create([
        'business_id' => $employee->business_id,
        'created_at' => Carbon::now()->subDays(rand(1, 100)),
        'employee_id' => $employee->external_id
      ]);

    factory(\App\Models\Transaction\UnassignedPurchasedItem::class)->create([
      'unassigned_transaction_id' => $unassigned[0]->id,
      'item_id' => $activeItem->id
    ]);

    $business = $unassigned[0]->business;
    $token = $this->createBusinessToken($business);
    $response = $this->send($token, 'get', '/api/business/unassigned-transactions')->getData();
    $storedTrans = \App\Models\Transaction\UnassignedTransaction::orderBy('created_at', 'desc')->get();
    foreach ($storedTrans as $key => $tran) {
      $this->assertSame($tran->created_at->toJson(), $response->data[$key]->transaction->created_at);
    }
  }

  public function test_a_business_can_only_retrieve_its_unassigned() {
    $unassignedBiz = factory(\App\Models\Transaction\UnassignedTransaction::class)->create();
    $numTrans = 7;
    $unassigned = factory(\App\Models\Transaction\UnassignedTransaction::class, $numTrans)
      ->create(['business_id' => $unassignedBiz->business_id]);
    $unassigned = $unassigned->push($unassignedBiz);

    factory(\App\Models\Transaction\UnassignedTransaction::class, 4)->create();
    $business = $unassignedBiz->business;

    $token = $this->createBusinessToken($business);
    $response = $this->send($token, 'get', '/api/business/unassigned-transactions')->getData();
    $this->assertEquals(count($unassigned), $response->meta->total);
  }

  public function test_a_business_can_retrieve_unassigned_by_date() {
    $startDate = urlencode(Carbon::now()->subDays(15)->toIso8601String());
    $endDate = urlencode(Carbon::now()->subDays(9)->toIso8601String());

    $unassignedBiz = factory(\App\Models\Transaction\UnassignedTransaction::class)->create(['created_at' => Carbon::now()->subDays(rand(9,15))]);
    $numTrans = 9;
    $unassigned = factory(\App\Models\Transaction\UnassignedTransaction::class, $numTrans)
      ->create(['business_id' => $unassignedBiz->business_id, 'created_at' => Carbon::now()->subDays(rand(10,14))]);
    $unassigned = $unassigned->push($unassignedBiz);

    factory(\App\Models\Transaction\UnassignedTransaction::class, 3)
      ->create(['business_id' => $unassignedBiz->business_id, 'created_at' => Carbon::now()->subDays(rand(16,25))]);

    factory(\App\Models\Transaction\UnassignedTransaction::class, 3)
      ->create(['business_id' => $unassignedBiz->business_id, 'created_at' => Carbon::now()->subDays(rand(0,8))]);

    $business = $unassignedBiz->business;

    $token = $this->createBusinessToken($business);
    $response = $this->send($token, 'get', "/api/business/unassigned-transactions?date[]={$startDate}&date[]={$endDate}")->getData();
    $this->assertEquals(count($unassigned), $response->meta->total);
  }
}
