<?php

namespace App\Observers\Business;

use App\Models\Business\PosAccount;
use App\Models\Business\PosAccountStatus;

class PosAccountObserver {
	public function creating(PosAccount $account) {
		$account->pos_account_status_id = (PosAccountStatus::where('code', 100)->first())->id;
	}
}
