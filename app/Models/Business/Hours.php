<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;

class Hours extends Model {

	//////////////////// Traits ////////////////////

	use \BinaryCabin\LaravelUUID\Traits\HasUUID;

	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $guarded = [];
	protected $hidden = ['id', 'profile_id', 'created_at', 'updated_at'];
	protected $uuidFieldName = 'identifier';

	//////////////////// Routing ////////////////////

	public function getRouteKeyName() {
		return 'identifier';
	}

	//////////////////// Relationships ////////////////////

	public function profile() {
		return $this->belongsTo('App\Models\Business\Profile');
	}
}
