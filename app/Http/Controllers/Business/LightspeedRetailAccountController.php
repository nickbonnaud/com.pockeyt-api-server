<?php

namespace App\Http\Controllers\Business;

use JWTAuth;
use Illuminate\Http\Request;
use App\Models\Business\Business;
use App\Http\Controllers\Controller;
use App\Models\Business\LightspeedRetailAccount;

class LightspeedRetailAccountController extends Controller {
  
  public function store(Request $request) {
  	if ($business = $this->authenticateRedirect($request)) {
	  	if ($code = $request->query('code')) {
	  		$lightspeedAccount = (new LightspeedRetailAccount())->authorizeRetail($code, $business);
	  		if ($lightspeedAccount) {
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
