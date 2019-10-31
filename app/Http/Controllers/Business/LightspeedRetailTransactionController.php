<?php

namespace App\Http\Controllers\Business;

use Illuminate\Http\Request;
use App\Models\Business\Business;
use App\Http\Controllers\Controller;
use App\Models\Transaction\Transaction;
use App\Http\Requests\Business\StoreLightspeedRetailRequest;

class LightspeedRetailTransactionController extends Controller {
  
  public function __construct() {
  	$this->middleware('auth:business');
  }

  public function store(StoreLightspeedRetailRequest $request) {
  	$business = Business::getAuthBusiness();
  	$lightspeedRetailAccount = $business->posAccount->lightspeedRetailAccount;

  	$lightspeedSale = $lightspeedRetailAccount->getSaleData($request->pos_transaction_id);
  	if ($lightspeedSale) {
  		$lightspeedRetailAccount->processTransaction($lightspeedSale, $request->customer_identifier);
  		return response()->json(['success' => 'Transaction created.'], 200);
  	}
  	return response()->json(['error' => 'Unable to create Transaction.'], 500);
  }
}
