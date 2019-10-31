<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Business\VendAccount;
use App\Http\requests\Webhook\VendRequest;

class VendController extends Controller {
  
  public function store(VendRequest $request) {
  	$vendAccount = VendAccount::where('domain_prefix', $request->domain_prefix)->first();
  	if ($request->type == "sale.update") {
  		$vendAccount->handleWebhook(json_decode($request->payload));
  	}
		return response()->json(['success' => 'Received.'], 200);
	}
}
