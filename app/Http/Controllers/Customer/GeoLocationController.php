<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Location\Region;
use App\Models\Customer\Customer;
use App\Models\Location\OnStartLocation;
use App\Http\Resources\Customer\BusinessResource;
use App\Http\Requests\Customer\StoreGeoLocationRequest;

class GeoLocationController extends Controller {
  
  public function __construct() {
  	$this->middleware('auth:customer');
  }

  public function store(StoreGeoLocationRequest $request) {
  	$customer = Customer::getAuthCustomer();
  	$region = Region::closestRegion($request->only(['lat', 'lng']));
  	OnStartLocation::createOnStartLocation($request->validated(), $customer, $region);
  	if (isset($region)) {
  		return BusinessResource::collection($region->locations);
  	}
  	return response()->json(['data' => []], 200);
  }

}
