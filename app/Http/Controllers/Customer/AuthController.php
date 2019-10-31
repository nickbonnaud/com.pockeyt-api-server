<?php

namespace App\Http\Controllers\Customer;

use App\Models\Customer\Customer;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\LoginCustomerRequest;

class AuthController extends Controller {

	public function __construct() {
  	$this->middleware('auth:customer')->only(['logout', 'refresh']);
  }

  public function register(StoreCustomerRequest $request) {
  	$customer = Customer::create($request->validated());
  	return $this->formatResponse(Customer::createToken($customer));
  }

  public function login(LoginCustomerRequest $request) {
    return $this->formatResponse(Customer::login($request->validated()));
  }

  public function logout() {
    return $this->formatResponse(Customer::logout());
  }

  public function refresh() {
    return $this->formatResponse(Customer::updateToken());
  }




  private function formatResponse($loginResult) {
    return response()->json([
      'data' => [
        'token' => Customer::formatToken($loginResult['token']),
      ],
      'errors' => [
        'email' => array($loginResult['error']),
        'password' => array($loginResult['error'])
      ]
    ], $loginResult['code']);
  }
}
