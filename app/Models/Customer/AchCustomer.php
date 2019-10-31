<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Model;

class AchCustomer extends Model {
  
  //////////////////// Attribute Mods/Helpers ////////////////////

	protected $fillable = ['customer_url', 'funding_source_url'];
	protected $hidden = ['id', 'customer_account_id', 'created_at', 'updated_at', 'customer_url', 'funding_source_url'];

	//////////////////// Relationships ////////////////////

	public function account() {
		return $this->belongsTo('App\Models\Customer\CustomerAccount', 'customer_account_id');
	}
}
