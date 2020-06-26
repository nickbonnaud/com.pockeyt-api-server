<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer\Customer;
use App\Models\Customer\CustomerPhoto;
use App\Models\Customer\CustomerProfile;
use App\Http\Requests\Customer\StorePhotoRequest;
use App\Http\Resources\Customer\CustomerResource;

class PhotoController extends Controller {

	public function __construct() {
  	$this->middleware('auth:customer');
  }

  public function store(CustomerProfile $customerProfile, StorePhotoRequest $request) {
  	if ($customerProfile->id != (Customer::getAuthCustomer())->profile->id) {
  		return response()->json(['errors' => 'Permission denied.'], 403);
  	}

  	$avatar = (new CustomerPhoto)->createPhoto($request->validated(), $customerProfile);
  	return new CustomerResource($customerProfile->fresh()->customer);
  }
}
