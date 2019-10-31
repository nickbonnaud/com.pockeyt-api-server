<?php

namespace App\Observers\Business;

use App\Models\Business\GeoAccount;
use App\Models\Business\BeaconAccount;

class GeoAccountObserver {

  public function creating(GeoAccount $geoAccount) {
  	$geoAccount->identifier = $geoAccount->location->identifier;
  }

  public function created(GeoAccount $geoAccount) {
  	BeaconAccount::createAccount($geoAccount->location, $geoAccount->identifier);
  }
}
