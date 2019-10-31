<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Model;

class CustomerProfile extends Model {

	//////////////////// Traits ////////////////////

	use \BinaryCabin\LaravelUUID\Traits\HasUUID;

	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $fillable = ['first_name', 'last_name'];
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

	public function photo() {
		return $this->hasOne('App\Models\Customer\CustomerProfilePhoto');
	}
}
