<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;

class PosAccountStatus extends Model {
  
  //////////////////// Attribute Mods/Helpers ////////////////////

	protected $fillable = ['name', 'code'];
	protected $hidden = ['id'];

	//////////////////// Relationships ////////////////////

	public function posAccounts() {
		return $this->hasMany('App\Models\Business\PosAccount');
	}
}
