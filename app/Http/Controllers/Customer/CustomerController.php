<?php

namespace App\Http\Controllers\Customer;

use App\Models\Customer\Customer;
use App\Http\Controllers\Controller;
use App\Http\Resources\Customer\CustomerResource;
use App\Http\Requests\Customer\UpdateCustomerRequest;

class CustomerController extends Controller {
  
  public function __construct() {
  	$this->middleware('auth:customer');
  }

  public function index() {
  	$customer = Customer::getAuthCustomer();
  	$customer['token'] = Customer::refreshToken();
  	return new CustomerResource($customer);
  }

  public function update(Customer $customer, UpdateCustomerRequest $request) {
  	if ($customer->id != (Customer::getAuthCustomer())->id) {
      return response()->json(['errors' => 'Permission denied.'], 403);
    }
  	$customer->update($request->validated());
  	$customer['token'] = Customer::refreshToken();
  	return new CustomerResource($customer);
  }
}
