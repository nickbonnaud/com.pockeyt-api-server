<?php

namespace App\Http\Controllers\Customer;

use App\Models\Customer\Customer;
use App\Http\Controllers\Controller;
use App\Http\Resources\Customer\PushTokenResource;
use App\Http\Requests\Customer\StorePushTokenRequest;

class PushTokenController extends Controller {
  
  public function __construct() {
  	$this->middleware('auth:customer');
  }

  public function store(StorePushTokenRequest $request) {
  	$customer = Customer::getAuthCustomer();
  	$customer->storePushToken($request->validated());
  	return new PushTokenResource($customer->pushToken);
  }
}
