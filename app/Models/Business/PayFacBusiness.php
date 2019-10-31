<?php

namespace App\Models\Business;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;

class PayFacBusiness extends Model {
  
  //////////////////// Traits ////////////////////

	use \BinaryCabin\LaravelUUID\Traits\HasUUID;

  //////////////////// Attribute Mods/Helpers ////////////////////

	protected $guarded = ['mcc', 'identifier'];
	protected $hidden = ['id', 'pay_fac_account_id', 'mcc'];
	protected $uuidFieldName = 'identifier';

	//////////////////// Routing ////////////////////

	public function getRouteKeyName() {
		return 'identifier';
	}

	//////////////////// Relationships ////////////////////

	public function payFacAccount() {
		return $this->belongsTo('App\Models\Business\PayFacAccount');
	}

	//////////////////// Core Methods ////////////////////

	public static function storeData($payFacAccountId, $payFacData) {
		$payFacData['pay_fac_account_id'] = $payFacAccountId;
		self::create($payFacData);
	}

	public function updateData($businessData) {
		$this->update(Arr::except($businessData, ['entity_type']));
		$this->payFacAccount->updatePayFacAccount(Arr::only($businessData, ['entity_type']));
	}

	public function getOwningBusiness() {
		return $this->payFacAccount->account->business;
	}
}
