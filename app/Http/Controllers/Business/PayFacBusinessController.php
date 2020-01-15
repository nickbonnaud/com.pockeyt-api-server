<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business\Business;
use App\Models\Business\Account;
use App\Models\Business\PayFacBusiness;
use App\Http\Resources\Business\PayFacBusinessResource;
use App\Http\Requests\Business\StorePayFacBusinessRequest;
use App\Http\Requests\Business\UpdatePayFacBusinessRequest;

class PayFacBusinessController extends Controller {
  
  public function __construct() {
  	$this->middleware('auth:business');
  }

  public function store(StorePayFacBusinessRequest $request) {
  	$business = Business::getAuthBusiness();
    $account = $business->account;
  	$account->createAccount($business, $request->entity_type);
  	$account->payFacAccount->storeData($request->except('entity_type'), $type = 'business');
  	return new PayFacBusinessResource($account->getPayFacBusiness());
  }

  public function update(PayFacBusiness $payFacBusiness, UpdatePayFacBusinessRequest $request) {
  	if ($payFacBusiness->getOwningBusiness()->id != Business::getAuthBusiness()->id) {
  		return response()->json(['errors' => 'Permission denied.'], 403);
  	}
  	$payFacBusiness->updateData($request->validated());
  	return new PayFacBusinessResource($payFacBusiness);
  }
}
