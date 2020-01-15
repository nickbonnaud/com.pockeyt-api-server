<?php

namespace App\Models\Business;

use App\Models\Business\AccountStatus;
use Illuminate\Database\Eloquent\Model;
use App\Models\Business\PayFacAccount;

class Account extends Model {
  
  //////////////////// Attribute Mods/Helpers ////////////////////

  protected $fillable = ['business_id'];
  protected $hidden = ['business_id', 'id', 'account_status_id'];

  //////////////////// Relationships ////////////////////

	public function status() {
		return $this->belongsTo('App\Models\Business\AccountStatus', 'account_status_id');
	}

	public function business() {
		return $this->belongsTo('App\Models\Business\Business');
	}

	public function payFacAccount() {
		return $this->hasOne('App\Models\Business\payFacAccount');
	}

	public function achAccount() {
		return $this->hasOne('App\Models\Business\AchAccount');
	}

	//////////////////// Core Methods ////////////////////

	public function createAccount($business, $entityType) {
		PayFacAccount::createPayFacAccount($business->account->id, $entityType);
	}

	public function getPayFacBusiness() {
		return $this->payFacAccount->payFacBusiness;
	}

	public function getPayFacOwners() {
		return $this->payFacAccount->payFacOwners;
	}

	public function getPayFacBank() {
		return $this->payFacAccount->payFacBank;
	}

	public function setStatus($code) {
		$this->account_status_id = AccountStatus::where('code', $code)->first()->id;
		$this->save();
	}
}
