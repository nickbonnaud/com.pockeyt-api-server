<?php

namespace App\Observers\Business;

use App\Models\Business\Inventory;
use App\Models\Business\ShopifyAccount;

class ShopifyAccountObserver {
  
	public function created(ShopifyAccount $account) {
		Inventory::create(['business_id' => $account->posAccount->business_id]);
		$account->createWebHooks();
	}
}
