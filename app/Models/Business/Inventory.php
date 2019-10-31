<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model {
  
  //////////////////// Attribute Mods/Helpers ////////////////////

	protected $fillable = ['business_id'];
  protected $hidden = ['business_id', 'id'];

  //////////////////// Relationships ////////////////////

  public function business() {
		return $this->belongsTo('App\Models\Business\Business');
	}

	public function activeItems() {
		return $this->hasMany('App\Models\Business\ActiveItem');
	}

	public function inactiveItems() {
		return $this->hasMany('App\Models\Business\InactiveItem');
	}
}
