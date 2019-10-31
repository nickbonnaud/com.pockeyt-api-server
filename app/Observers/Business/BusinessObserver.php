<?php

namespace App\Observers\Business;

use App\Models\Business\Business;
use Illuminate\Support\Facades\Hash;

class BusinessObserver {
	
  public function creating(Business $business) {
    $business->password = Hash::make($business->password);
  }

  public function updating(Business $business) {
    if ($business->isDirty('password')) {
      $business->password = Hash::make($business->password);
    }
  }
}
