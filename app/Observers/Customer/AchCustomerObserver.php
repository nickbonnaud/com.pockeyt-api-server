<?php

namespace App\Observers\Customer;

use App\Models\Customer\AchCustomer;

class AchCustomerObserver {

	public function created(AchCustomer $achCustomer) {
		$achCustomer->account->update(['primary' => 'ach']);
		if ($achCustomer->account->customer->status->code == 103) {
			$achCustomer->account->customer->setStatus(120);
		}
	}
}
