<?php

namespace App\Observers\Business;

use App\Models\Business\SquareAccount;

class SquareAccountObserver {
  
  public function creating(SquareAccount $account) {
  	$account->location_id = $account->fetchLocationId();
  }

  public function created(SquareAccount $account) {
  	$account->fetchInventoryItems();
  	$account->createWebHook();
  }
}
