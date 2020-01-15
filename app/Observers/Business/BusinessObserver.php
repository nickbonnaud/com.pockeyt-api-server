<?php

namespace App\Observers\Business;

use App\Models\Business\Business;
use App\Models\Business\Account;
use Illuminate\Support\Facades\Hash;

class BusinessObserver {
	
  public function creating(Business $business) {
    $business->password = Hash::make($business->password);
  }

  public function created(Business $business) {
  	Account::create(['business_id' => $business->id]);
  }

  public function updating(Business $business) {
    if ($business->isDirty('password')) {
      $business->password = Hash::make($business->password);
    }
  }
}
