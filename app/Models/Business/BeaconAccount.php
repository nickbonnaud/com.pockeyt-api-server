<?php

namespace App\Models\Business;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class BeaconAccount extends Model {

	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $guarded = ['id'];
	protected $hidden = ['location_id', 'id', 'created_at', 'updated_at'];
	protected $casts = ['major' => 'integer', 'minor' => 'integer'];

	//////////////////// Relationships ////////////////////

	public function location() {
		return $this->belongsTo('App\Models\Business\Location');
	}

	//////////////////// Core Methods ////////////////////

	public static function createAccount($location) {
		$minor = self::generateMinor($location->major);
		self::create([
			'location_id' => $location->id,
			'identifier' => $location->region->identifier,
			'major' => $location->major,
			'minor' => $minor
		]);
	}

	private static function generateMinor($major) {
		$minor = mt_rand(0, 65535);
		if (self::where('major', $major)->where('minor', $minor)->exists()) {
			return self::generateMinor($major);
		}
		return $minor;
	}
}
