<?php

namespace App\Http\Controllers\Customer;

use App\Models\Customer\Customer;
use App\Models\Business\Location;
use App\Models\Location\ActiveLocation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreLocationRequest;
use App\Http\Resources\Location\ActiveLocationResource;

class LocationController extends Controller {
  
  public function __construct() {
  	$this->middleware('auth:customer');
  }

  public function store(Location $location, StoreLocationRequest $request) {
  	$customer = Customer::getAuthCustomer();
  	$activeLocation = ActiveLocation::createLocation($customer, $location, $request->validated());
  	return new ActiveLocationResource($activeLocation);
  }

  public function update(ActiveLocation $activeLocation, StoreLocationRequest $request) {
  	if ((Customer::getAuthCustomer())->id != $activeLocation->customer_id) {
  		return response()->json(['errors' => 'Permission denied.'], 403);
  	}
  	$activeLocation->updateLocation($request->validated());
  	return new ActiveLocationResource($activeLocation);
  }

  public function destroy(ActiveLocation $activeLocation, StoreLocationRequest $request) {
  	if ((Customer::getAuthCustomer())->id != $activeLocation->customer_id) {
  		return response()->json(['errors' => 'Permission denied.'], 403);
  	}
  	$activeLocation->destroyLocation($request->validated());
  	if ($activeLocation = $activeLocation->fresh()) {
  		return new ActiveLocationResource($activeLocation);
  	}
  	return response()->json(['data' => ['success' => 'Location removed.']], 200);
  }
}
