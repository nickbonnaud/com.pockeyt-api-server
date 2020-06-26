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
  protected $hidden = ['business_id', 'id', 'created_at', 'updated_at'];
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
		$location = self::create(['business_id' => $business->id, 'region_id' => $region->id]);
		return $location;
	}

	public function updateLocation($coords) {
		$region = self::getRegion($coords);
		$this->update(['region_id' => $region->id]);
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
}
