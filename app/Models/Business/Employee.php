<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model {
  
  //////////////////// Traits ////////////////////

	use \BinaryCabin\LaravelUUID\Traits\HasUUID;

	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $guarded = [];
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

	public function transactions() {
		return $this->hasMany('App\Models\Transaction\Transaction', 'employee_id', 'external_id');
	}

	public function unassignedTransactions() {
		return $this->hasMany('App\Models\Transaction\UnassignedTransaction', 'employee_id', 'external_id');
	}
}
