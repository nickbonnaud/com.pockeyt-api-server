<?php

namespace App\Observers\Business;

use App\Models\Business\PayFacBank;

class PayFacBankObserver {

	public function created(PayFacBank $bank) {
		$bank->payFacAccount->account->setStatus(105);
	}
}
