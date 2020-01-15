<?php

namespace App\Http\Controllers\Business;

use Illuminate\Http\Request;
use App\Models\Business\Business;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Business\StoreBusinessRequest;
use App\Http\Requests\Business\LoginBusinessRequest;
use App\Http\Resources\Business\DashboardBusinessResource;

class AuthController extends Controller {
  
  public function __construct() {
  	$this->middleware('auth:business')->only(['logout', 'refresh', 'verify']);
  } 

  public function register(StoreBusinessRequest $request) {
  	$business = Business::create($request->only('email', 'password'));
  	return $this->formatResponse(Business::createToken($business), $business);
  }

  public function login(LoginBusinessRequest $request) {
    $tokenData = Business::login($request->only('email', 'password'));
    $business = Business::getAuthBusiness();
    return $this->formatResponse($tokenData, $business);
  }

  public function logout() {
    return $this->formatResponse(Business::logout());
  }

  public function refresh() {
    return $this->formatResponse(Business::updateToken());
  }

  public function verify(Request $request) {
    $business = Business::getAuthBusiness();
    return response()->json(['data' => ['password_verified' => Hash::check($request->password, $business->password)]]);
  }





  private function formatResponse($loginResult, $business = null) {
    return response()->json([
      'data' => [
        'token' => $loginResult['token'],
        'business' => $business != null ? new DashboardBusinessResource($business) : null
      ],
      'errors' => [
        'email' => array($loginResult['error']),
        'password' => array($loginResult['error'])
      ]
    ], $loginResult['code']);
  }
}
