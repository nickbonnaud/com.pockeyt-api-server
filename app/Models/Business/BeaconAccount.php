<?php

namespace App\Models\Business;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class BeaconAccount extends Model {

	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $guarded = ['id'];
	protected $hidden = ['location_id', 'id', 'created_at', 'updated_at'];

	//////////////////// Relationships ////////////////////

	public function location() {
		return $this->belongsTo('App\Models\Business\Location');
	}

	//////////////////// Core Methods ////////////////////

	public static function createAccount($location, $identifier) {
		self::create([
			'location_id' => $location->id,
			'identifier' => $identifier,
			'major' => $location->business->identifier,
			'minor' => null
		]);
	}
}
