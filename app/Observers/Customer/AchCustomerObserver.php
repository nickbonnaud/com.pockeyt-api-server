<?php

namespace App\Observers\Customer;

use App\Models\Customer\AchCustomer;

class AchCustomerObserver {

	public function created(AchCustomer $achCustomer) {
		$achCustomer->account->update(['primary' => 'ach']);
	}
}
