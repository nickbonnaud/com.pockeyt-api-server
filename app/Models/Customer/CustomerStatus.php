<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Model;

class CustomerStatus extends Model {
  
  //////////////////// Attribute Mods/Helpers ////////////////////

	protected $fillable = ['name', 'code'];
	protected $hidden = ['id', 'created_at', 'updated_at'];

  //////////////////// Relationships ////////////////////

  public function customers() {
  	return $this->hasMany('App\Models\Customer\Customer');
  }
}
