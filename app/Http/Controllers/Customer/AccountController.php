<?php

namespace App\Http\Controllers\Customer;

use App\Models\Customer\Customer;
use App\Http\Controllers\Controller;
use App\Models\Customer\CustomerAccount;
use App\Http\Resources\Customer\CustomerAccountResource;
use App\Http\requests\Customer\UpdateCustomerAccountRequest;

class AccountController extends Controller {
  
  public function __construct() {
  	$this->middleware('auth:customer');
  }

  public function update(CustomerAccount $customerAccount, UpdateCustomerAccountRequest $request) {
  	if ($customerAccount->id != (Customer::getAuthCustomer())->account->id) {
  		return response()->json(['errors' => 'Permission denied.'], 403);
  	}
  	$customerAccount->update($request->validated());
  	return new CustomerAccountResource($customerAccount);
  }
}
