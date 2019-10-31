<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;

class AchBusiness extends Model {

	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $guarded = ['business_url'];
	protected $hidden = ['id', 'ach_account_id', 'business_url'];

	//////////////////// Relationships ////////////////////

	public function achAccount() {
		return $this->belongsTo('App\Models\Business\AchAccount');
	}

	//////////////////// Core Methods ////////////////////

	public static function storeData($achData) {
		self::create($achData);
	}

	public function updateData($achData) {
		$this->update($achData);
	}
}
