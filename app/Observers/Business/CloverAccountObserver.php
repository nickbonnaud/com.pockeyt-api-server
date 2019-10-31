<?php

namespace App\Observers\Business;

use App\Models\Business\CloverAccount;

class CloverAccountObserver {
  
  public function created(CloverAccount $account) {
  	$account->fetchInventoryItems();
  }
}
