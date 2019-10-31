<?php

namespace App\Http\Controllers\Business;

use JWTAuth;
use Illuminate\Http\Request;
use App\Models\Business\Business;
use App\Models\Business\SquareAccount;
use App\Http\Controllers\Controller;

class SquareAccountController extends Controller {
  
  public function store(Request $request) {
    if ($business = $this->authenticateRedirect($request)) {
      if ($code = $request->query('code')) {
        $squareAccount = (new SquareAccount())->authorizeSquare($request->code, $business);
        if ($squareAccount) {
          return redirect()->away(config('urls.dashboard.base') . '?oauth=success');
        }
      }
    }
    return redirect()->away(config('urls.dashboard.base') . '?oauth=fail');
  }


  private function authenticateRedirect($request) {
    if ($request->query('state')) {
      try {
        $businessId = (JWTAuth::setToken($request->state))->getPayload()->get('sub');
      } catch(\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
        return null;
      }
      return Business::where('id', $businessId)->first();
    }
    return null;
  }
}
