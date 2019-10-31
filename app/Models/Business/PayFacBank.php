<?php

namespace App\Models\Business;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class PayFacBank extends Model {

	 //////////////////// Traits ////////////////////

	use \BinaryCabin\LaravelUUID\Traits\HasUUID;

	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $guarded = ['identifier'];
	protected $hidden = ['id', 'pay_fac_account_id', 'routing_number', 'account_number'];
	protected $uuidFieldName = 'identifier';

	//////////////////// Routing ////////////////////

	public function getRouteKeyName() {
		return 'identifier';
	}

	//////////////////// Relationships ////////////////////

	public function payFacAccount() {
		return $this->belongsTo('App\Models\Business\PayFacAccount');
	}

	//////////////////// Mutator Methods ////////////////////

	public function setRoutingNumberAttribute($value) {
		if (!Str::contains($value, 'X')) {
			$this->attributes['routing_number'] = encrypt($value);
		}
	}

	public function setAccountNumberAttribute($value) {
		if (!Str::contains($value, 'X')) {
			$this->attributes['account_number'] = encrypt($value);
		}
	}

	//////////////////// Accessor Methods ////////////////////

	public function getRoutingNumberAttribute($value) {
		return decrypt($value);
	}

	public function getAccountNumberAttribute($value) {
		return decrypt($value);
	}

	//////////////////// Core Methods ////////////////////

	public static function storeData($payFacAccountId, $payFacData) {
		$payFacData['pay_fac_account_id'] = $payFacAccountId;
		self::create($payFacData);
	}

	public function updateData($bankData) {
		$this->update($bankData);
	}

	public function getOwningBusiness() {
		return $this->payFacAccount->account->business;
	}
}
