<?php

namespace Tests\Unit\Transaction;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PurchasedItemTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_a_purchased_item_belongs_to_a_transaction() {
  	$transaction = factory(\App\Models\Transaction\Transaction::class)->create();
  	$purchasedItem = factory(\App\Models\Transaction\PurchasedItem::class)->create(['transaction_id' => $transaction->id]);
  	$this->assertInstanceOf('App\Models\Transaction\PurchasedItem', $transaction->purchasedItems->first());
  }

  public function test_a_transaction_can_have_multiple_purchased_items() {
  	$transaction = factory(\App\Models\Transaction\Transaction::class)->create();
  	$purchasedItem = factory(\App\Models\Transaction\PurchasedItem::class, 5)->create(['transaction_id' => $transaction->id]);
  	$this->assertEquals(5, $transaction->purchasedItems->count());
  }

  public function test_a_purchased_item_has_one_transaction() {
  	$transaction = factory(\App\Models\Transaction\Transaction::class)->create();
  	$purchasedItem = factory(\App\Models\Transaction\PurchasedItem::class)->create(['transaction_id' => $transaction->id]);
  	$this->assertInstanceOf('App\Models\Transaction\Transaction', $purchasedItem->transaction);
  }

  public function test_a_purchased_item_belongs_to_an_active_item() {
  	$activeItem = factory(\App\Models\Business\ActiveItem::class)->create();
  	$purchasedItem = factory(\App\Models\Transaction\PurchasedItem::class)->create(['item_id' => $activeItem->id]);
  	$this->assertInstanceOf('App\Models\Transaction\PurchasedItem', $activeItem->purchasedItems->first());
  }

  public function test_an_active_item_can_have_multiple_purchased_items() {
  	$activeItem = factory(\App\Models\Business\ActiveItem::class)->create();
  	$purchasedItem = factory(\App\Models\Transaction\PurchasedItem::class, 3)->create(['item_id' => $activeItem->id]);
  	$this->assertEquals(3, $activeItem->purchasedItems->count());
  }

  public function test_a_purchased_item_has_one_active_item() {
  	$activeItem = factory(\App\Models\Business\ActiveItem::class)->create();
  	$purchasedItem = factory(\App\Models\Transaction\PurchasedItem::class)->create(['item_id' => $activeItem->id]);
  	$this->assertInstanceOf('App\Models\Business\ActiveItem', $purchasedItem->activeItem);
  }

  public function test_a_purchased_item_belongs_to_an_inactive_item() {
  	$inactiveItem = factory(\App\Models\Business\InactiveItem::class)->create();
  	$purchasedItem = factory(\App\Models\Transaction\PurchasedItem::class)->create(['item_id' => $inactiveItem->active_id]);
  	$this->assertInstanceOf('App\Models\Transaction\PurchasedItem', $inactiveItem->purchasedItems->first());
  }

  public function test_an_inactive_item_can_have_multiple_purchased_items() {
  	$inactiveItem = factory(\App\Models\Business\InactiveItem::class)->create();
  	$purchasedItem = factory(\App\Models\Transaction\PurchasedItem::class, 6)->create(['item_id' => $inactiveItem->active_id]);
  	$this->assertEquals(6, $inactiveItem->purchasedItems->count());
  }

  public function test_a_purchased_item_has_one_inactive_item() {
  	$inactiveItem = factory(\App\Models\Business\InactiveItem::class)->create();
  	$purchasedItem = factory(\App\Models\Transaction\PurchasedItem::class)->create(['item_id' => $inactiveItem->active_id]);
  	$this->assertInstanceOf('App\Models\Business\InactiveItem', $purchasedItem->inactiveItem);
  }

  public function test_a_purchased_item_returns_inventory_item_if_active() {
  	$activeItem = factory(\App\Models\Business\ActiveItem::class)->create();
  	$purchasedItem = factory(\App\Models\Transaction\PurchasedItem::class)->create(['item_id' => $activeItem->id]);
  	$this->assertInstanceOf('App\Models\Business\ActiveItem', $purchasedItem->getInventoryItem());
  }

  public function test_a_purchased_item_returns_inventory_item_if_inactive() {
  	$inactiveItem = factory(\App\Models\Business\InactiveItem::class)->create();
  	$purchasedItem = factory(\App\Models\Transaction\PurchasedItem::class)->create(['item_id' => $inactiveItem->active_id]);
  	$this->assertInstanceOf('App\Models\Business\InactiveItem', $purchasedItem->getInventoryItem());
  }
}
