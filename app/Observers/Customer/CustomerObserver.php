<?php

namespace App\Observers\Customer;

use App\Models\Customer\Customer;
use Illuminate\Support\Facades\Hash;

class CustomerObserver {

	public function creating(Customer $customer) {
		$customer->password = Hash::make($customer->password);
	}

	public function updating(Customer $customer) {
		if ($customer->isDirty('password')) {
      $customer->password = Hash::make($customer->password);
    }
	}
}
