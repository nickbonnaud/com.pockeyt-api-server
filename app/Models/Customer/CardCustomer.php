<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Model;

class CardCustomer extends Model {

  //////////////////// Traits ////////////////////

	use \BinaryCabin\LaravelUUID\Traits\HasUUID;

	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $fillable = ['shopper_reference', 'recurring_detail_reference'];
	protected $hidden = ['id', 'customer_account_id', 'created_at', 'updated_at', 'shopper_reference', 'recurring_detail_reference'];
	protected $uuidFieldName = 'shopper_reference';

	//////////////////// Relationships ////////////////////

	public function account() {
		return $this->belongsTo('App\Models\Customer\CustomerAccount', 'customer_account_id');
	}

	public function pay($transaction) {
		
	}
}
