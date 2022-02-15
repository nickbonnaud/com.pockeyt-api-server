<?php

namespace App\Http\Controllers\Customer;

use App\Models\Customer\Customer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\Customer\CustomerResource;
use App\Http\Requests\Customer\StoreCustomerRequest;
use App\Http\Requests\Customer\LoginCustomerRequest;
use App\Http\Requests\Customer\CheckPasswordRequest;
use App\Http\Requests\Customer\RequestPasswordResetRequest;
use App\Http\Requests\Business\ResetPasswordRequest;

class AuthController extends Controller {

	public function __construct() {
  	$this->middleware('auth:customer')->only(['logout', 'refresh', 'check']);
  }

  public function register(StoreCustomerRequest $request) {
  	$customer = Customer::create($request->validated());
    $registerData = Customer::createToken($customer);
    $customer['token'] = $registerData['token'];
  	return $this->formatResponse($registerData, $customer);
  }

  public function login(LoginCustomerRequest $request) {
    $loginData = Customer::login($request->validated());
    $customer = Customer::getAuthCustomer();
    if ($customer != null) {
      $customer['token'] = $loginData['token'];
    }
    return $this->formatResponse($loginData, $customer);
  }

  public function logout() {
    return $this->formatResponse(Customer::logout());
  }

  public function refresh() {
    $tokenData = Customer::updateToken();
    $customer = Customer::getAuthCustomer();
    $customer['token'] = $tokenData['token'];
    return $this->formatResponse($tokenData, $customer);
  }

  public function check(CheckPasswordRequest $request) {
    $customer = Customer::getAuthCustomer();
    $isCorrectPassword = Hash::check($request->password, $customer->password);
    return response()->json([
      'data' => [
        'password_verified' => $isCorrectPassword
      ]
    ]);
  }

  public function requestResetPassword(RequestPasswordResetRequest $request) {
    $customer = Customer::where('email', $request->email)->first();
    $customer->sendResetCode();

    return response()->json([
      'data' => [
        'email_sent' => true
      ]
    ]);
  }

  public function resetPassword(ResetPasswordRequest $request) {
    $customer = Customer::where('email', $request->email)->first();
    $customer->resetPassword($request->only('password'));

    return response()->json([
      'data' => [
        'password_reset' => true
      ]
    ]);
  }



  private function formatResponse($loginResult, $customer = null) {
    return response()->json([
      'data' => $customer != null ? new CustomerResource($customer) : null,
      'errors' => [
        'email' => array($loginResult['error']),
        'password' => array($loginResult['error'])
      ]
    ], $loginResult['code']);
  }
}
