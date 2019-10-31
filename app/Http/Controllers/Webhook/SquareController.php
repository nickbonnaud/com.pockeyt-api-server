<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Business\SquareAccount;
use App\Http\requests\Webhook\SquareRequest;

class SquareController extends Controller {

	public function store(SquareRequest $request) {
		$squareAccount = SquareAccount::where('merchant_id', $request->merchant_id)
			->where('location_id', $request->location_id)
			->first();

		$squareAccount->fetchPayment($request->validated());
		return response()->json(['success' => 'Received.'], 200);
	}
}
