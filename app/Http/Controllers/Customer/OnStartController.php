<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Location\OnStartLocation;
use App\Models\Customer\Customer;
use App\Models\Business\Location;
use App\Http\Resources\Business\LocationResource;
use App\Http\Requests\Customer\StoreOnStartRequest;

class OnStartController extends Controller {
  
  public function __construct() {
  	$this->middleware('auth:customer');
  }

  public function store(StoreOnStartRequest $request) {
  	$customer = Customer::getAuthCustomer();
  	$regionId = (OnStartLocation::createOnStartLocation($request->validated(), $customer))->region_id;

  	return LocationResource::collection(Location::getLocationsFromAttribute('region_id', $regionId));
  }
}
