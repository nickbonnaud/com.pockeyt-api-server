<?php

namespace App\Observers\Business;

use App\Models\Business\Account;
use App\Models\Business\AccountStatus;

class AccountObserver {
  public function creating(Account $account) {
  	$account->account_status_id = (AccountStatus::where('code', 100)->first())->id;
  }
}
