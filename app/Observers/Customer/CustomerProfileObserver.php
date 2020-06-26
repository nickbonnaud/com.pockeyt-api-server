<?php

namespace App\Observers\Customer;

use App\Models\Customer\CustomerProfile;

class CustomerProfileObserver {
	public function created(CustomerProfile $profile) {
		$profile->customer->setStatus(101);
	}
}