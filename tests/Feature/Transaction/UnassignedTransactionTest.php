<?php

namespace Tests\Feature\Transaction;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Transaction\Transaction;
use App\Models\Transaction\PurchasedItem;
use App\Models\Transaction\UnassignedPurchasedItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UnassignedTransactionTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_deleting_an_unassigned_transaction_creates_transaction_an_purchased_items() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $unassignedTransaction = factory(\App\Models\Transaction\UnassignedTransaction::class)->create();
    $unassignedTransaction['customer_id'] = $customer->id;

    factory(\App\Models\Transaction\UnassignedPurchasedItem::class, 6)->create(['unassigned_transaction_id' => $unassignedTransaction->id]);

    $unassignedTransaction->delete();
    $this->assertEquals(6, PurchasedItem::count());
    $this->assertEquals(0, UnassignedPurchasedItem::count());
    $transaction = Transaction::where('business_id', $unassignedTransaction->business_id)->first();
    $this->assertEquals(6, $transaction->PurchasedItems->count());
  }
}
