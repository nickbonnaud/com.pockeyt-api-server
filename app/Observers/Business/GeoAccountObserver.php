<?php

namespace App\Observers\Business;

use App\Models\Business\GeoAccount;
use App\Models\Business\BeaconAccount;

class GeoAccountObserver {

  public function creating(GeoAccount $geoAccount) {
  	$geoAccount->identifier = $geoAccount->location->identifier;
  }

  public function saved(GeoAccount $geoAccount) {
  	if ($geoAccount->location->business->account->status->code == 105) {
  		$geoAccount->location->business->account->setStatus(106);
  	}
  }

  public function created(GeoAccount $geoAccount) {
  	BeaconAccount::createAccount($geoAccount->location, $geoAccount->identifier);
  }
}
