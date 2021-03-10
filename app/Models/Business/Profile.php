<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model {

	//////////////////// Traits ////////////////////

	use \BinaryCabin\LaravelUUID\Traits\HasUUID;

	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $fillable = ['name', 'website', 'description', 'google_place_id', 'phone', 'hours'];
	protected $hidden = ['id', 'business_id'];
	protected $uuidFieldName = 'identifier';

	//////////////////// Routing ////////////////////

	public function getRouteKeyName() {
		return 'identifier';
	}

  //////////////////// Relationships ////////////////////

	public function business() {
		return $this->belongsTo('App\Models\Business\Business');
	}

	public function photos() {
		return $this->hasOne('App\Models\Business\ProfilePhotos');
	}

	public function hours() {
		return $this->hasOne('App\Models\Business\Hours');
	}

	//////////////////// Relationship Methods ////////////////////

	public function storeHours($hours) {
		$this->hours()->save($hours);
		return $this->hours;
	}

	public function updateHours($hoursData) {
		$this->hours()->update($hoursData);
		return $this->hours;
	}
}
