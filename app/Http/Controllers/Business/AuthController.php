<?php

namespace App\Http\Controllers\Business;

use App\Models\Business\Business;
use App\Http\Controllers\Controller;
use App\Http\Requests\Business\StoreBusinessRequest;
use App\Http\Requests\Business\LoginBusinessRequest;

class AuthController extends Controller {
  
  public function __construct() {
  	$this->middleware('auth:business')->only(['logout', 'refresh']);
  } 

  public function register(StoreBusinessRequest $request) {
  	$business = Business::create($request->only('email', 'password'));
  	return $this->formatResponse(Business::createToken($business));
  }

  public function login(LoginBusinessRequest $request) {
    return $this->formatResponse(Business::login($request->only('email', 'password')));
  }

  public function logout() {
    return $this->formatResponse(Business::logout());
  }

  public function refresh() {
    return $this->formatResponse(Business::updateToken());
  }





  private function formatResponse($loginResult) {
    return response()->json([
      'data' => [
        'token' => Business::formatToken($loginResult['token']),
      ],
      'errors' => [
        'email' => array($loginResult['error']),
        'password' => array($loginResult['error'])
      ]
    ], $loginResult['code']);
  }
}
