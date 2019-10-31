<?php

namespace App\Http\Controllers\Business;

use JWTAuth;
use Illuminate\Http\Request;
use App\Models\Business\Business;
use App\Models\Business\CloverAccount;
use App\Http\Controllers\Controller;

class CloverAccountController extends Controller {

  public function store(Request $request) {
    if ($business = $this->authenticateRedirect($request)) {
      $code = $request->query('code');
      $merchantId = $request->query('merchant_id');
      if ($code && $merchantId) {
        $cloverAccount = (new CloverAccount())->authorizeClover($code, $merchantId, $business);
        if ($cloverAccount) {
          return redirect()->away(config('urls.dashboard.base') . '?oauth=success');
        }
      }
    }
    return redirect()->away(config('urls.dashboard.base') . '?oauth=fail');
  }

  private function authenticateRedirect($request) {
    if ($request->has('state')) {
      try {
        $businessId = (JWTAuth::setToken($request->state))->getPayload()->get('sub');
      } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
        return null;
      }
      return Business::where('id', $businessId)->first();
    }
    return null;
  }
}
