<?php

namespace App\Observers\Customer;

use App\Models\Customer\CustomerAccount;

class CustomerAccountObserver {

	public function updated(CustomerAccount $customerAccount) {
		if ($customerAccount->customer->status->code == 102) {
			$customerAccount->customer->setStatus(103);
		}
	}
}