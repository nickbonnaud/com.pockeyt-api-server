<?php

namespace App\Http\Controllers\Webhook;

use App\Models\Business\CloverAccount;
use App\Http\Controllers\Controller;
use App\Http\requests\Webhook\CloverRequest;

class CloverController extends Controller {

	public function store(CloverRequest $request) {
		if ($request->has('verificationCode')) {
			if (env('APP_ENV') != 'testing') {
				\Log::debug("Verify Code: {$request->verificationCode}");
			}
			return response()->json(['success' => 'authorized'], 200);
		}
		foreach ($request->merchants as $merchantId => $merchantUpdates) {
			(CloverAccount::where('merchant_id', $merchantId)->first())->handleWebhook($merchantUpdates);
		}
		return response()->json(['success' => 'Received.'], 200);
	}
}
