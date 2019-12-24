<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class UnassignedPurchasedItem extends Model {

	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $fillable = ['item_id'];
	protected $hidden = ['id', 'unassigned_transaction_id', 'created_at', 'updated_at'];

	//////////////////// Relationships ////////////////////

	public function transaction() {
		return $this->belongsTo('App\Models\Transaction\UnassignedTransaction', 'unassigned_transaction_id');
	}

	public function activeItem() {
		return $this->belongsTo('App\Models\Business\ActiveItem', 'item_id');
	}

	public function inactiveItem() {
		return $this->belongsTo('App\Models\Business\InactiveItem', 'item_id', 'active_id');
	}

	//////////////////// Core Methods ////////////////////

	public function getInventoryItem() {
		$item = $this->activeItem;
		if ($item) {
			return $item;
		} else {
			return $this->inactiveItem;
		}
	}
}
