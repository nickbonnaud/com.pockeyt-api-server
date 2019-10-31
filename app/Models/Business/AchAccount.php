<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;

class AchAccount extends Model {

	//////////////////// Traits ////////////////////

	use \BinaryCabin\LaravelUUID\Traits\HasUUID;

	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $fillable = ['account_id', 'business_type'];
	protected $hidden = ['account_id', 'id', 'funding_url'];
	protected $uuidFieldName = 'identifier';

	//////////////////// Routing ////////////////////

	public function getRouteKeyName() {
		return 'identifier';
	}

	//////////////////// Relationships ////////////////////

	public function account() {
		return $this->belongsTo('App\Models\Business\Account');
	}

	public function achBusiness() {
		return $this->hasOne('App\Models\Business\AchBusiness');
	}

	public function achOwners() {
		return $this->hasMany('App\Models\Business\AchOwner');
	}

	//////////////////// Core Methods ////////////////////

	public static function createAchAccount($payFacAccount) {
		self::createAccount(['account_id' => $payFacAccount->account_id, 'business_type' => $payFacAccount->entity_type]);
	}

	public static function createAccount($achData) {
		self::create($achData);
	}

	public function updateAchData($payFacAccount) {
		$this->updateData(['business_type' => $payFacAccount->entity_type]);
	}

	public function updateData($achData) {
		$this->update($achData);
	}
}
