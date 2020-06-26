<?php

namespace App\Observers\Customer;

use App\Models\Customer\Customer;
use Illuminate\Support\Facades\Hash;
use App\Models\Customer\CustomerStatus;

class CustomerObserver {

	public function creating(Customer $customer) {
		$customer->password = Hash::make($customer->password);
		$customer->customer_status_id = CustomerStatus::where('code', 100)->first()->id;
	}

	public function created(Customer $customer) {
		$customer->account()->create();
	}

	public function updating(Customer $customer) {
		if ($customer->isDirty('password')) {
      $customer->password = Hash::make($customer->password);
    }
	}
}
