<?php

namespace App\Observers\Business;

use App\Models\Business\Inventory;
use App\Models\Business\LightspeedRetailAccount;

class LightspeedRetailAccountObserver {

	public function created(LightspeedRetailAccount $account) {
		Inventory::create(['business_id' => $account->posAccount->business_id]);
		if (env('APP_ENV') != 'testing') {
			$account->createPaymentType();
		}
	}
}
