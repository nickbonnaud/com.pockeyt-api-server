<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Business\ShopifyAccount;
use App\Http\requests\Webhook\ShopifyRequest;

class ShopifyController extends Controller {

	public function store(ShopifyRequest $request) {
		$shopifyAccount = ShopifyAccount::where('shop_id', $request->header('x-shopify-shop-domain'))->first();

		if ($request->header('x-shopify-topic') == "orders/paid") {
			$shopifyAccount->createTransaction($request->all());
		} else {
			$shopifyAccount->createRefund($request->all());
		}
		
		return response()->json(['success' => 'Received.'], 200); 
	}
}
