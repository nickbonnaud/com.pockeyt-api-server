<?php

namespace App\Http\Controllers\Business;

use App\Models\Business\Business;
use App\Models\Business\PayFacBank;
use App\Http\Controllers\Controller;
use App\Http\Resources\Business\PayFacBankResource;
use App\Http\Requests\Business\StorePayFacBankRequest;
use App\Http\Requests\Business\UpdatePayFacBankRequest;

class PayFacBankController extends Controller {
  
  public function __construct() {
  	$this->middleware('auth:business');
  }

  public function store(StorePayFacBankRequest $request) {
  	$business = Business::getAuthBusiness();
  	$business->account->payFacAccount->storeData($request->validated(), $type = 'bank');
  	return new PayFacBankResource($business->fresh()->account->getPayFacBank());
  }

  public function update(UpdatePayFacBankRequest $request, PayFacBank $payFacBank) {
  	$business = Business::getAuthBusiness();
  	if ($payFacBank->getOwningBusiness()->id != $business->id) {
  		return response()->json(['errors' => 'Permission denied.'], 403);
  	}
  	$payFacBank->updateData($request->validated());
  	return new PayFacBankResource($payFacBank);
  }
}
