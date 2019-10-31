<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Model;

class PushToken extends Model {
  
  //////////////////// Attribute Mods/Helpers ////////////////////

	protected $fillable = ['device', 'token'];
	protected $hidden = ['id', 'customer_id', 'created_at', 'updated_at'];

	 //////////////////// Relationships ////////////////////

	public function customer() {
		return $this->belongsTo('App\Models\Customer\Customer');
	}
}
