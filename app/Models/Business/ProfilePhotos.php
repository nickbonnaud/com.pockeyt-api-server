<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;

class ProfilePhotos extends Model {

	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $fillable = ['profile_id', 'logo_id', 'banner_id'];

	//////////////////// Relationships ////////////////////

	public function profile() {
		return $this->belongsTo('App\Models\Business\Profile'); 
	}

	public function logo() {
		return $this->belongsTo('App\Models\Business\Photo', 'logo_id'); 
	}

	public function banner() {
		return $this->belongsTo('App\Models\Business\Photo', 'banner_id'); 
	}
}
