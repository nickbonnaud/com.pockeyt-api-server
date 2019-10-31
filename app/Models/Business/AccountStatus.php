<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;

class AccountStatus extends Model {
  
  //////////////////// Attribute Mods/Helpers ////////////////////

	protected $fillable = ['name', 'code'];
	protected $hidden = ['id'];

  //////////////////// Relationships ////////////////////

	public function accounts() {
		return $this->hasMany('App\Models\Business\Account');
	}
}
