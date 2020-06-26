<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Location\OnStartLocation;
use App\Models\Customer\Customer;
use App\Http\Resources\Customer\BusinessResource;
use App\Http\Requests\Customer\StoreOnStartRequest;

class OnStartController extends Controller {
  
  public function __construct() {
  	$this->middleware('auth:customer');
  }

  public function store(StoreOnStartRequest $request) {
  	$customer = Customer::getAuthCustomer();
  	$region = (OnStartLocation::createOnStartLocation($request->validated(), $customer))->region;
  	if (isset($region)) {
  		return BusinessResource::collection($region->locations);
  	}
  	return response()->json([
  		'data' => []
  	], 200);
  }
}
