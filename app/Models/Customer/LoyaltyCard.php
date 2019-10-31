<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Model;

class LoyaltyCard extends Model {

	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $guarded = ['outstanding_rewards', 'total_rewards_earned'];
	protected $hidden = ['id', 'customer_id', 'loyalty_program_id', 'created_at', 'updated_at'];

	//////////////////// Relationships ////////////////////

	public function customer() {
		return $this->belongsTo('App\Models\Customer\Customer');
	}

	public function loyaltyProgram() {
		return $this->belongsTo('App\Models\Business\LoyaltyProgram');
	}
}
