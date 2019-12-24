<?php

namespace App\Models\Business;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class PayFacOwner extends Model {
  
 //////////////////// Traits ////////////////////

	use \BinaryCabin\LaravelUUID\Traits\HasUUID;

  //////////////////// Attribute Mods/Helpers ////////////////////

	protected $guarded = ['identifier'];
	protected $hidden = ['id', 'pay_fac_account_id', 'ssn', 'gender'];
	protected $casts = ['primary' => 'boolean'];
	protected $uuidFieldName = 'identifier';

	protected $dates = ['dob'];

	//////////////////// Routing ////////////////////

	public function getRouteKeyName() {
		return 'identifier';
	}

	//////////////////// Relationships ////////////////////

	public function payFacAccount() {
		return $this->belongsTo('App\Models\Business\PayFacAccount');
	}

	//////////////////// Mutator Methods ////////////////////

	public function setSsnAttribute($value) {
		if (!Str::contains(strtoupper($value), 'X')) {
			$this->attributes['ssn'] = encrypt($value);
		}
	}

	public function setPercentOwnershipAttribute($value) {
		$this->attributes['percent_ownership'] = $value * 100;
	}

	//////////////////// Accessor Methods ////////////////////

	public function getSsnAttribute($value) {
		return decrypt($value);
	}

	public function getPercentOwnershipAttribute($value) {
		return $value / 100;
	}

	//////////////////// Core Methods ////////////////////

	public static function storeData($payFacAccountId, $payFacData) {
		$payFacData['pay_fac_account_id'] = $payFacAccountId;
		return self::create($payFacData);
	}

	public function updateData($ownerData) {
		$this->update($ownerData);
	}

	public function getOwningBusiness() {
		return $this->payFacAccount->account->business;
	}
}
