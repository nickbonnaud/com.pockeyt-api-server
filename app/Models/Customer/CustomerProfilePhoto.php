<?php

namespace App\models\Customer;

use Illuminate\Database\Eloquent\Model;

class CustomerProfilePhoto extends Model {
  
  //////////////////// Attribute Mods/Helpers ////////////////////

  protected $fillable = ['customer_profile_id', 'avatar_id'];

  //////////////////// Relationships ////////////////////

  public function profile() {
		return $this->belongsTo('App\Models\Customer\CustomerProfile', 'customer_profile_id'); 
	}

	public function avatar() {
		return $this->belongsTo('App\Models\Customer\CustomerPhoto', 'avatar_id'); 
	}
}
