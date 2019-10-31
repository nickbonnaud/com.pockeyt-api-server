<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Model;

class CustomerAccount extends Model {
  
  //////////////////// Traits ////////////////////

	use \BinaryCabin\LaravelUUID\Traits\HasUUID;

	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $fillable = ['tip_rate', 'primary'];
	protected $hidden = ['id', 'customer_id', 'created_at', 'updated_at'];
	protected $uuidFieldName = 'identifier';

	//////////////////// Routing ////////////////////

	public function getRouteKeyName() {
		return 'identifier';
	}

	//////////////////// Relationships ////////////////////

	public function customer() {
		return $this->belongsTo('App\Models\Customer\Customer');
	}

	public function ach() {
		return $this->hasOne('App\Models\Customer\AchCustomer');
	}

	public function card() {
		return $this->hasOne('App\Models\Customer\CardCustomer');
	}
}
