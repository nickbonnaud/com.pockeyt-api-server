<?php

namespace App\Http\Controllers\Customer;

use App\Models\Customer\Customer;
use App\Models\Business\BeaconAccount;
use App\Models\Location\ActiveLocation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreLocationRequest;
use App\Http\Resources\Location\ActiveLocationResource;

class LocationController extends Controller {
  
  public function __construct() {
  	$this->middleware('auth:customer');
  }

  public function store(StoreLocationRequest $request) {
  	$customer = Customer::getAuthCustomer();
    $beaconAccount = BeaconAccount::where('identifier', $request->beacon_identifier)->first();
    $activeLocation = ActiveLocation::createLocation($customer, $beaconAccount->location);
  	return new ActiveLocationResource($activeLocation);
  }

  public function destroy(ActiveLocation $activeLocation) {
  	if ((Customer::getAuthCustomer())->id != $activeLocation->customer_id) {
  		return response()->json(['errors' => 'Permission denied.'], 403);
  	}
  	$activeLocation->destroyLocation();
  	if ($activeLocation = $activeLocation->fresh()) {
      return response()->json(['data' => ['deleted' => false]], 200);
  	}
  	return response()->json(['data' => ['deleted' => true]], 200);
  }
}
