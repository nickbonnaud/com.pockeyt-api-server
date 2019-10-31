<?php

namespace App\Observers\Business;

use App\Models\Business\Inventory;
use App\Models\Business\VendAccount;

class VendAccountObserver {

	public function created(VendAccount $account) {
		Inventory::create(['business_id' => $account->posAccount->business_id]);
		$account->createWebhook();
	}
}
