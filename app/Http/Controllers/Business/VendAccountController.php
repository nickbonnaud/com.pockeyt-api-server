<?php

namespace App\Http\Controllers\Business;

use JWTAuth;
use Illuminate\Http\Request;
use App\Models\Business\Business;
use App\Models\Business\VendAccount;
use App\Http\Controllers\Controller;

class VendAccountController extends Controller {

	public function store(Request $request) {
		if ($request->has('code')) {
			return $this->getAccessToken($request);
		} else {
			return $this->redirectOauth($request);
		}
	}



	private function getAccessToken($request) {
		if ($business = $this->authenticateRedirect($request)) {
			if ($request->has('domain_prefix')) {
				(new VendAccount())->authorizeVend($request->domain_prefix, $request->code, $business);
				return redirect()->away(config('urls.dashboard.base') . '?oauth=success');
			}
		}
		return redirect()->away(config('urls.dashboard.base') . '?oauth=fail');
	}

	private function authenticateRedirect($request) {
		if ($request->has('state')) {
			try {
				$businessId = (JWTAuth::setToken($request->state))->getPayload()->get('sub');
			} catch(\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
				return null;
			}
			return Business::where('id', $businessId)->first();
		}
		return null;
	}

	private function redirectOauth($request) {
		if ($business = Business::getAuthBusiness()) {
			$clientId = env('VEND_CLIENT_ID');
			$redirectUrl = url('/api/business/pos/vend/oauth');
			$state = auth('business')->getToken();
			$url = "https://secure.vendhq.com/connect?response_type=code&client_id={$clientId}&redirect_uri={$redirectUrl}&state={$state}";
			return redirect($url);
		}
		return redirect()->away(config('urls.dashboard.base') . '/auth/login');
	}
}
