<?php

namespace App\Models\Location;

use Illuminate\Support\Arr;
use App\Models\Location\Region;
use App\Models\Business\Location;
use Illuminate\Database\Eloquent\Model;

class OnStartLocation extends Model {
  
  //////////////////// Attribute Mods/Helpers ////////////////////

  protected $guarded = [];
  protected $hidden = ['id'];

  //////////////////// Relationships ////////////////////

  public function customer() {
		return $this->belongsTo('App\Models\Customer\Customer');
	}

	public function region() {
		return $this->belongsTo('App\Models\Location\Region');
	}

	public function location() {
		return $this->belongsTo('App\Models\Business\Location');
	}

	//////////////////// Core Methods ////////////////////

	public static function createOnStartLocation($onStartData, $customer) {
		$location = Arr::has($onStartData, ['location_identifier']) ? self::getLocation($onStartData) : null;

		$region = $location ? $location->region : Region::closestRegion(Arr::only($onStartData, ['lat', 'lng']));
		return self::create([
			'customer_id' => $customer->id,
			'region_id' => optional($region)->id,
			'location_id' => optional($location)->id,
			'lat' => $onStartData['lat'],
			'lng' => $onStartData['lng'],
			'beacon_start' => $onStartData['beacon_start']
		]);
	}

	public static function getLocation($onStartData) {
		return Location::getLocationFromAttribute('identifier', $onStartData['location_identifier']);
	}

}
