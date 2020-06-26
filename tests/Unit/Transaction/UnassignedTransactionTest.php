<?php

namespace Tests\Unit\Transaction;

use Tests\TestCase;
use App\Models\Transaction\Transaction;
use App\Models\Transaction\UnassignedTransaction;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UnassignedTransactionTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unassigned_transaction_creates_a_unique_identifier() {
    $unassignedTransaction = factory(\App\Models\Transaction\UnassignedTransaction::class)->create();
    $this->assertNotNull($unassignedTransaction->identifier);
  }

  public function test_an_unassigned_transaction_belongs_to_a_business() {
  	$business = factory(\App\Models\Business\Business::class)->create();
  	$unassignedTransaction = factory(\App\Models\Transaction\UnassignedTransaction::class)->create(['business_id' => $business->id]);
  	$this->assertInstanceOf('App\Models\Transaction\UnassignedTransaction', $business->unassignedTransactions->first());
  }

  public function test_a_business_has_many_unassigned_unassigned_transactions() {
  	$business = factory(\App\Models\Business\Business::class)->create();
  	$unassignedTransaction = factory(\App\Models\Transaction\UnassignedTransaction::class, 4)->create(['business_id' => $business->id]);
  	$this->assertEquals(4, $business->unassignedTransactions->count());
  }

  public function test_an_unassigned_transactions_has_one_business() {
  	$business = factory(\App\Models\Business\Business::class)->create();
  	$unassignedTransaction = factory(\App\Models\Transaction\UnassignedTransaction::class)->create(['business_id' => $business->id]);
  	$this->assertInstanceOf('App\Models\Business\Business', $unassignedTransaction->business);
  }

  public function test_deleting_an_unassigned_transaction_creates_a_new_transaction() {
  	$customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
  	$unassignedTransaction = factory(\App\Models\Transaction\UnassignedTransaction::class)->create();
  	$unassignedTransaction['customer_id'] = $customer->id;

  	$unassignedTransaction->delete();

  	$this->assertDatabaseHas('transactions', ['business_id' => $unassignedTransaction->business_id, 'customer_id' => $customer->id]);
  	$this->assertEquals(1, Transaction::count());
  	$this->assertEquals(0, UnassignedTransaction::count());
  }
}
