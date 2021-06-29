<?php

namespace App\Http\Controllers\Customer;

use App\Models\Customer\Customer;
use App\Http\Controllers\Controller;
use App\Models\Customer\CustomerProfile;
use App\Http\Resources\Customer\ProfileResource;
use App\Http\Resources\Customer\CustomerResource;
use App\Http\Requests\Customer\StoreProfileRequest;

class ProfileController extends Controller {

  public function __construct() {
  	$this->middleware('auth:customer');
  }

  public function index() {
  	$customer = Customer::getAuthCustomer();
  	return new ProfileResource($customer->profile);
  }

  public function store(StoreProfileRequest $request) {
  	$customer = Customer::getAuthCustomer();
  	$customer->storeProfile(new CustomerProfile($request->validated()));
  	return new CustomerResource($customer->fresh());
  }

  public function update(CustomerProfile $customerProfile, StoreProfileRequest $request) {
  	if ($customerProfile->id != (Customer::getAuthCustomer())->profile->id) {
  		return response()->json(['errors' => 'Permission denied.'], 403);
  	}
  	$customerProfile->update($request->validated());
		return new CustomerResource($customerProfile->customer->fresh());
  }
}
