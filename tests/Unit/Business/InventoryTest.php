<?php

namespace Tests\Unit\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InventoryTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_inventory_belongs_to_a_business() {
  	$business = factory(\App\Models\Business\Business::class)->create();
  	$inventory = factory(\App\Models\Business\Inventory::class)->create(['business_id' => $business->id]);
  	$this->assertInstanceOf('App\Models\Business\Inventory', $business->inventory);
  }

  public function test_a_business_has_one_inventory() {
  	$business = factory(\App\Models\Business\Business::class)->create();
  	$inventory = factory(\App\Models\Business\Inventory::class)->create(['business_id' => $business->id]);
  	$this->assertInstanceOf('App\Models\Business\Business', $inventory->business);
  }
}
