<?php

namespace App\Models\Location;

use Illuminate\Support\Arr;
use App\Helpers\TestHelpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Region extends Model {
  
  //////////////////// Traits ////////////////////

	use \BinaryCabin\LaravelUUID\Traits\HasUUID;

	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $guarded = ['identifier'];
	protected $hidden = [ 'id', 'created_at', 'updated_at', 'center_lat', 'center_lng'];
	protected $uuidFieldName = 'identifier';

	//////////////////// Routing ////////////////////

	public function getRouteKeyName() {
		return 'identifier';
	}

	//////////////////// Relationships ////////////////////

	public function locations() {
		return $this->hasMany('App\Models\Business\Location');
	}

	public function onStartLocations() {
		return $this->hasMany('App\Models\Location\OnStartLocation');
	}

	//////////////////// Mutator Methods ////////////////////

	public function setCityAttribute($city) {
		$this->attributes['city'] = strtolower($city);
	}

	public function setStateAttribute($state) {
		$this->attributes['state'] = strtolower($state);
	}

	public function setNeighborhoodAttribute($neighborhood) {
		$this->attributes['neighborhood'] = strtolower($neighborhood);
	}

	//////////////////// Core Methods ////////////////////

	public static function checkExists($regionData) {
    return self::retrieveRegion($regionData)->exists();
	}

	public static function getRegion($regionData) {
		return self::retrieveRegion($regionData)->first();
	}

	public static function retrieveRegion($regionData) {
		$region = self::where('city', strtolower($regionData['city']))
    	->where('state', strtolower($regionData['state']))
      ->where('zip', $regionData['zip']);
      if (Arr::has($regionData, 'neighborhood')) {
      	$region->where('neighborhood', strtolower($regionData['neighborhood']));
      }
    return $region;
	}

	public static function closestRegion($coords) {
		if (env('APP_ENV') == 'testing') {
			return TestHelpers::fakeRegionClosest($coords);
		} else {
			return self::select(DB::raw("ST_Distance_Sphere(
															point(center_lat, center_lng),
															point({$coords['lat']}, {$coords['lng']})
														) / 1000 AS distance"))
														->having('distance', '<', 20)
														->orderBy('distance')
														->first();
		}
	}
}