<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class PurchasedItem extends Model {
  
  //////////////////// Attribute Mods/Helpers ////////////////////

	protected $fillable = ['item_id'];
	protected $hidden = ['id', 'transaction_id'];

	//////////////////// Relationships ////////////////////

	public function transaction() {
		return $this->belongsTo('App\Models\Transaction\Transaction');
	}

	public function activeItem() {
		return $this->belongsTo('App\Models\Business\ActiveItem', 'item_id');
	}

	public function inactiveItem() {
		return $this->belongsTo('App\Models\Business\inactiveItem', 'item_id', 'active_id');
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
