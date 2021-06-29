<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use App\Helpers\TestHelpers;

class GeoAccount extends Model {

	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $fillable = ['location_id', 'lat', 'lng', 'radius', 'identifier'];
	protected $hidden = ['location_id', 'id', 'created_at', 'updated_at'];
	protected $casts = ['lat' => 'double', 'lng' => 'double', 'radius' => 'integer'];

	//////////////////// Routing ////////////////////

	public function getRouteKeyName() {
		return 'identifier';
	}

	//////////////////// Relationships ////////////////////

	public function location() {
		return $this->belongsTo('App\Models\Business\Location');
	}

	//////////////////// Core Methods ////////////////////

	public function updateAccount($geoAccountData) {
		$this->update($geoAccountData);
		$this->location->updateLocation(['lat' => $this->lat, 'lng' => $this->lng]);
	}
}
