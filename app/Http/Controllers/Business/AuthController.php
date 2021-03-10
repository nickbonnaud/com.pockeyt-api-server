<?php

namespace App\Http\Controllers\Business;

use Carbon\Carbon;
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
    $this->middleware('csrf')->only(['logout', 'refresh', 'verify']);
  }

  public function register(StoreBusinessRequest $request) {
  	$business = Business::register($request->only('email', 'password'));
  	return $this->formatResponse($business);
  }

  public function login(LoginBusinessRequest $request) {
    return $this->formatResponse(auth('business')->user());
  }

  public function logout() {
    Business::logout();
    return response()->json([
    'data' => [
        'success' => true
      ]
    ], 200);
  }

  public function refresh() {
    $business = auth('business')->user();
    Business::refreshToken($business);
    return $this->formatResponse($business);
  }

  public function verify(Request $request) {
    $passwordValid = Hash::check($request->password, auth('business')->user()->password);
    return response()->json([
      'data' => [
        'password_verified' => $passwordValid
      ]], $passwordValid ? 200 : 401
    );
  }



  private function formatResponse($business) {
    return response()
      ->json([
        'data' => [
          'csrf_token' => [
            'value' => auth('business')->payload()->get('csrf-token'),
            'expiry' => Carbon::now()->addMinutes(config('jwt.ttl'))
          ],
          'business' => new DashboardBusinessResource($business)
        ]
      ], 200)->cookie('jwt', auth('business')->getToken()->get(), config('jwt.ttl'));
  }
}
