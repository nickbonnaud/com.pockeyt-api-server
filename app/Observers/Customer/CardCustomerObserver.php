<?php

namespace App\Observers\Customer;

use App\Models\Customer\CardCustomer;

class CardCustomerObserver {
  public function created(CardCustomer $cardCustomer) {
		$cardCustomer->account->update(['primary' => 'card']);
	}
}
