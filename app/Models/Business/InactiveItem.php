<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;

class InactiveItem extends Model {
  
  //////////////////// Attribute Mods/Helpers ////////////////////

	protected $guarded = [];
	protected $hidden = [ 'id', 'inventory_id', 'active_id'];

	//////////////////// Relationships ////////////////////

	public function inventory() {
		return $this->belongsTo('App\Models\Business\Inventory');
	}

	public function purchasedItems() {
		return $this->hasMany('App\Models\Transaction\PurchasedItem', 'item_id', 'active_id');
	}

	public function unassignedPurchasedItems() {
		return $this->hasMany('App\Models\Transaction\UnassignedTransactionPurchasedItem', 'item_id');
	}

	//////////////////// Core Methods ////////////////////

	public static function createInactiveItem($activeItem) {
		$activeId = $activeItem->id;
		$inventoryId = $activeItem->inventory_id;
		$inactiveItem = $activeItem->toArray();
		$inactiveItem['active_id'] = $activeId;
		$inactiveItem['inventory_id'] = $inventoryId;
		self::create($inactiveItem);
	}
}
