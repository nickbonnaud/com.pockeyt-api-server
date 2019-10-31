<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;

class LoyaltyProgram extends Model {
 
  //////////////////// Traits ////////////////////

	use \BinaryCabin\LaravelUUID\Traits\HasUUID;

	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $fillable = ['purchases_required', 'amount_required', 'reward', 'business_id'];
	protected $hidden = ['id', 'business_id', 'created_at', 'updated_at'];
	protected $uuidFieldName = 'identifier';

	//////////////////// Routing ////////////////////

	public function getRouteKeyName() {
		return 'identifier';
	}

	//////////////////// Relationships ////////////////////

	public function business() {
		return $this->belongsTo('App\Models\Business\Business');
	}

	public function loyaltyCards() {
		return $this->hasMany('App\Models\Customer\LoyaltyCard');
	}

	//////////////////// Core Methods ////////////////////

	public function destroyProgram() {
		$this->delete();
	}
}
