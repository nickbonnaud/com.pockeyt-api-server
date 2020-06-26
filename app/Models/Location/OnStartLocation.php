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

	//////////////////// Core Methods ////////////////////

	public static function createOnStartLocation($data, $customer, $region) {
		if ($data['start_location']) {
			self::create([
				'customer_id' => $customer->id,
				'region_id' => optional($region)->id,
				'lat' => $data['lat'],
				'lng' => $data['lng'],
			]);
		}
	}
}
