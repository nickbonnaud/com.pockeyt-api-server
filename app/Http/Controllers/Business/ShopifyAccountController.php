<?php

namespace App\Http\Controllers\Business;

use JWTAuth;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Business\Business;
use App\Http\Controllers\Controller;
use App\Models\Business\ShopifyAccount;

class ShopifyAccountController extends Controller {

	public function store(Request $request) {
		if ($this->validateHmac($request)) {
			if ($request->has('code')) {
				return $this->getAccessToken($request);
			} else {
				return $this->redirectOauth($request);
			}
		}
		return redirect()->away(config('urls.dashboard.base') . '?oauth=fail');
	}

	


	private function getAccessToken($request) {
		if ($business = $this->authenticateRedirect($request)) {
			if ($this->validateShop($request)) {
				$shopifyAccount = (new ShopifyAccount())->authorizeShopify($request->shop, $request->code, $business);
				return redirect()->away(config('urls.dashboard.base') . '?oauth=success');
			}
		}
		return redirect()->away(config('urls.dashboard.base') . '?oauth=fail');
	}

	private function redirectOauth($request) {
		if ($business = Business::getAuthBusiness()) {
			$apiKey = env('SHOPIFY_CLIENT_ID');
		  $scopes = "read_products,read_orders,read_draft_orders";
		  $redirectUrl = url("/api/business/pos/shopify/oauth");
		  $state = auth('business')->getToken();

		  $url = "https://{$request->shop}/admin/oauth/authorize?client_id={$apiKey}&scope={$scopes}&redirect_uri={$redirectUrl}&state={$state}";
		  return redirect($url);
		}
		return redirect()->away(config('urls.dashboard.base') . '/auth/login');
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

	private function validateHmac($request) {
		if ($request->has('hmac')) {
			$queryUrl = "";
			foreach ($request->except('hmac') as $key => $value) {
				$queryUrl = "{$queryUrl}{$key}={$value}&";
			}
			$queryUrl = hash_hmac('sha256', substr($queryUrl, 0, -1), env('SHOPIFY_SECRET'));
			return hash_equals($queryUrl, $request->hmac);
		}
		return false;
	}

	private function validateShop($request) {
		if ($request->has('shop')) {
			return Str::endsWith($request->shop, '.myshopify.com');
		}
		return false;
	}
}
