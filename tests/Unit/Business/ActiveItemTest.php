<?php

namespace Tests\Unit\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActiveItemTest extends TestCase {
	use WithFaker, RefreshDatabase;

	public function test_an_active_item_belongs_to_an_inventory() {
		$inventory = factory(\App\Models\Business\Inventory::class)->create();
		$activeItem = factory(\App\Models\Business\ActiveItem::class)->create(['inventory_id' => $inventory->id]);
		$this->assertInstanceOf('App\Models\Business\ActiveItem', $inventory->activeItems->first());
	}

	public function test_an_inventory_can_have_many_active_items() {
		$inventory = factory(\App\Models\Business\Inventory::class)->create();
		$activeItem = factory(\App\Models\Business\ActiveItem::class, 2)->create(['inventory_id' => $inventory->id]);
		$this->assertEquals(2, $inventory->activeItems->count());
	}

	public function test_an_active_item_has_one_inventory() {
		$inventory = factory(\App\Models\Business\Inventory::class)->create();
		$activeItem = factory(\App\Models\Business\ActiveItem::class)->create(['inventory_id' => $inventory->id]);
		$this->assertInstanceOf('App\Models\Business\Inventory', $activeItem->inventory);
	}
}
