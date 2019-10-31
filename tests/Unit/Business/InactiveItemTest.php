<?php

namespace Tests\Unit\Business;

use Tests\TestCase;
use App\Models\Business\InactiveItem;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InactiveItemTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function test_an_inactive_item_belongs_to_an_inventory() {
		$inventory = factory(\App\Models\Business\Inventory::class)->create();
		$inactiveItem = factory(\App\Models\Business\InactiveItem::class)->create(['inventory_id' => $inventory->id]);
		$this->assertInstanceOf('App\Models\Business\InactiveItem', $inventory->inactiveItems->first());
	}

	public function test_an_inventory_can_have_many_inactive_items() {
		$inventory = factory(\App\Models\Business\Inventory::class)->create();
		$inactiveItem = factory(\App\Models\Business\InactiveItem::class, 2)->create(['inventory_id' => $inventory->id]);
		$this->assertEquals(2, $inventory->inactiveItems->count());
	}

	public function test_an_inactive_item_has_one_inventory() {
		$inventory = factory(\App\Models\Business\Inventory::class)->create();
		$inactiveItem = factory(\App\Models\Business\InactiveItem::class)->create(['inventory_id' => $inventory->id]);
		$this->assertInstanceOf('App\Models\Business\Inventory', $inactiveItem->inventory);
	}

	public function test_deleting_an_active_item_creates_an_inactive_item() {
		$activeItem = factory(\App\Models\Business\ActiveItem::class)->create();
		$activeId = $activeItem->id;
		$activeItem->delete();
		$this->assertDatabaseHas('inactive_items', ['active_id' => $activeId]);
	}
}
