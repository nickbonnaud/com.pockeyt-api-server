<?php

namespace App\Observers\Customer;

use App\Models\Customer\CardCustomer;

class CardCustomerObserver {
  public function created(CardCustomer $cardCustomer) {
		$cardCustomer->account->update(['primary' => 'card']);

		if ($cardCustomer->account->customer->status->code == 103) {
			$cardCustomer->account->customer->setStatus(120);
		}
	}
}
