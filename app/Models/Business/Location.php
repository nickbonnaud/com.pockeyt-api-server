<?php

namespace App\Models\Business;

use App\Models\Business\GeoAccount;
use App\Models\Location\Region;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Model;

class Location extends Model {

  //////////////////// Traits ////////////////////

	use \BinaryCabin\LaravelUUID\Traits\HasUUID;

  //////////////////// Attribute Mods/Helpers ////////////////////

 	protected $guarded = [];
  protected $hidden = ['business_id', 'id', 'major', 'created_at', 'updated_at'];
  protected $uuidFieldName = 'identifier';

  //////////////////// Routing ////////////////////

	public function getRouteKeyName() {
		return 'identifier';
	}

  //////////////////// Relationships ////////////////////

  public function business() {
		return $this->belongsTo('App\Models\Business\Business');
	}

	public function region() {
		return $this->belongsTo('App\Models\Location\Region');
	}

	public function geoAccount() {
		return $this->hasOne('App\Models\Business\GeoAccount');
	}

	public function beaconAccount() {
		return $this->hasOne('App\Models\Business\BeaconAccount');
	}

	public function activeCustomers() {
		return $this->hasMany('App\Models\Location\ActiveLocation');
	}

	public function historicCustomers() {
		return $this->hasMany('App\Models\Location\HistoricLocation');
	}

	//////////////////// Core Methods ////////////////////

	public static function createLocation($coords, $business) {
		$region = self::getRegion($coords);
		$major = self::generateMajor();
		$location = self::create([
			'business_id' => $business->id,
			'region_id' => $region->id,
			'major' => $major
		]);
		return $location;
	}



	public function updateLocation($coords) {
		$region = self::getRegion($coords);
		$this->update(['region_id' => $region->id]);
	}

	public function getDistance($lat2, $lng2) {
		$lat1 = $this->geoAccount->lat;
		$lng1 = $this->geoAccount->lng;
		$R = 6371; // Radius of the earth in km
	  $dLat = $this->deg2rad($lat2 - $lat1);  // deg2rad below
	  $dLng = $this->deg2rad($lng2 - $lng1);
	  $a = sin($dLat / 2) * sin($dLat / 2) + cos($this->deg2rad($lat1)) * cos($this->deg2rad($lat2)) * sin($dLng / 2) * sin($dLng / 2);
	  $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
	  return $R * $c; // Distance in km
	}

	public static function getRegion($coords) {
		return Region::closestRegion($coords);
	}

	public static function getLocationFromAttribute($attribute, $value) {
		return self::where($attribute, $value)->first();
	}

	public static function getLocationsFromAttribute($attribute, $value) {
		return self::where($attribute, $value)->get();
	}

	private function deg2rad($deg) {
		return $deg * (pi() / 180);
	}

	private static function generateMajor() {
		$major = mt_rand(0, 65535);
		if (self::where('major', $major)->exists()) {
			return self::generateMajor();
		}
		return $major;
	}
}
