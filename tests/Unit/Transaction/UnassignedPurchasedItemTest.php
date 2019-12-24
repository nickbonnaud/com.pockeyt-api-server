<?php

namespace Tests\Unit\Transaction;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UnassignedPurchasedItemTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unassigned_purchased_item_belongs_to_unassigned_transaction() {
  	$unassignedTransaction = factory(\App\Models\Transaction\UnassignedTransaction::class)->create();
  	$unassignedItem = factory(\App\Models\Transaction\UnassignedPurchasedItem::class)->create(['unassigned_transaction_id' => $unassignedTransaction->id]);
  	$this->assertInstanceOf('App\Models\Transaction\UnassignedPurchasedItem', $unassignedTransaction->purchasedItems->first());
  }

  public function test_an_unassigned_transaction_can_have_multiple_items() {
  	$unassignedTransaction = factory(\App\Models\Transaction\UnassignedTransaction::class)->create();
  	$unassignedItem = factory(\App\Models\Transaction\UnassignedPurchasedItem::class, 6)->create(['unassigned_transaction_id' => $unassignedTransaction->id]);
  	$this->assertEquals(6, $unassignedTransaction->purchasedItems->count());
  }

  public function test_an_unassigned_purchased_item_has_one_unassigned_transaction() {
  	$unassignedTransaction = factory(\App\Models\Transaction\UnassignedTransaction::class)->create();
  	$unassignedItem = factory(\App\Models\Transaction\UnassignedPurchasedItem::class)->create(['unassigned_transaction_id' => $unassignedTransaction->id]);
  	$this->assertInstanceOf('App\Models\Transaction\UnassignedTransaction', $unassignedItem->transaction);
  }

  public function test_an_unassigned_purchased_item_belongs_to_an_active_item() {
  	$activeItem = factory(\App\Models\Business\ActiveItem::class)->create();
  	$unassignedItem = factory(\App\Models\Transaction\UnassignedPurchasedItem::class)->create(['item_id' => $activeItem->id]);
  	$this->assertInstanceOf('App\Models\Transaction\UnassignedPurchasedItem', $activeItem->unassignedPurchasedItems->first());
  }

  public function test_an_active_item_can_have_multiple_unassigned_purchased_items() {
  	$activeItem = factory(\App\Models\Business\ActiveItem::class)->create();
  	$unassignedItem = factory(\App\Models\Transaction\UnassignedPurchasedItem::class, 5)->create(['item_id' => $activeItem->id]);
  	$this->assertEquals(5, $activeItem->unassignedPurchasedItems->count());
  }

  public function test_an_unassigned_purchased_item_has_one_active_item() {
  	$activeItem = factory(\App\Models\Business\ActiveItem::class)->create();
  	$unassignedItem = factory(\App\Models\Transaction\UnassignedPurchasedItem::class)->create(['item_id' => $activeItem->id]);
  	$this->assertInstanceOf('App\Models\Business\ActiveItem', $unassignedItem->activeItem);
  }

  public function test_an_unassigned_purchased_item_belongs_to_an_inactive_item() {
  	$inactiveItem = factory(\App\Models\Business\InactiveItem::class)->create();
  	$unassignedItem = factory(\App\Models\Transaction\UnassignedPurchasedItem::class)->create(['item_id' => $inactiveItem->id]);
  	$this->assertInstanceOf('App\Models\Transaction\UnassignedPurchasedItem', $inactiveItem->unassignedPurchasedItems->first());
  }

  public function test_an_inactive_item_can_have_multiple_unassigned_items() {
  	$inactiveItem = factory(\App\Models\Business\InactiveItem::class)->create();
  	$unassignedItem = factory(\App\Models\Transaction\UnassignedPurchasedItem::class, 5)->create(['item_id' => $inactiveItem->id]);
  	$this->assertEquals(5, $inactiveItem->unassignedPurchasedItems->count());
  }

  public function test_an_unassigned_item_has_one_inactive_item() {
  	$inactiveItem = factory(\App\Models\Business\InactiveItem::class)->create();
  	$unassignedItem = factory(\App\Models\Transaction\UnassignedPurchasedItem::class)->create(['item_id' => $inactiveItem->active_id]);
  	$this->assertInstanceOf('App\Models\Business\InactiveItem', $unassignedItem->inactiveItem);
  }

  public function test_an_unassigned_item_returns_inventory_item_if_active() {
  	$activeItem = factory(\App\Models\Business\ActiveItem::class)->create();
  	$unassignedItem = factory(\App\Models\Transaction\UnassignedPurchasedItem::class)->create(['item_id' => $activeItem->id]);
  	$this->assertInstanceOf('App\Models\Business\ActiveItem', $unassignedItem->getInventoryItem());
  }

  public function test_an_unassigned_item_returns_item_if_inactive() {
  	$inactiveItem = factory(\App\Models\Business\InactiveItem::class)->create();
  	$unassignedItem = factory(\App\Models\Transaction\UnassignedPurchasedItem::class)->create(['item_id' => $inactiveItem->active_id]);
  	$this->assertInstanceOf('App\Models\Business\InactiveItem', $unassignedItem->getInventoryItem());
  }
}
