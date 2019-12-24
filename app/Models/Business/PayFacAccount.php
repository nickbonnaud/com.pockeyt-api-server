<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;
use App\Models\Business\PayFacBusiness;
use App\Models\Business\PayFacOwner;
use App\Models\Business\PayFacBank;

class PayFacAccount extends Model {

	//////////////////// Traits ////////////////////

	use \BinaryCabin\LaravelUUID\Traits\HasUUID;

	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $fillable = ['account_id', 'entity_type'];
	protected $hidden = ['account_id', 'id', 'account_code'];
	protected $uuidFieldName = 'identifier';

	//////////////////// Routing ////////////////////

	public function getRouteKeyName() {
		return 'identifier';
	}

	//////////////////// Relationships ////////////////////

	public function account() {
		return $this->belongsTo('App\Models\Business\Account');
	}

	public function payFacBusiness() {
		return $this->hasOne('App\Models\Business\PayFacBusiness');
	}

	public function payFacOwners() {
		return $this->hasMany('App\Models\Business\PayFacOwner');
	}

	public function payFacBank() {
		return $this->hasOne('App\Models\Business\PayFacBank');
	}

	//////////////////// Core Methods ////////////////////

	public static function createPayFacAccount($accountId, $entityType) {
		self::create(['account_id' => $accountId, 'entity_type' => $entityType]);
	}

	public function updatePayFacAccount($accountData) {
		$this->update($accountData);
	}

	public function storeData($payFacData, $type) {
		if ($type == 'business') {
			PayFacBusiness::storeData($this->id, $payFacData);
		} elseif ($type == 'owner') {
			return PayFacOwner::storeData($this->id, $payFacData);
		} elseif ($type == 'bank') {
			PayFacBank::storeData($this->id, $payFacData);
		}
	}


}
