<?php

namespace App\Http\Controllers\Business;

use App\Models\Business\Business;
use App\Models\Business\PayFacOwner;
use App\Http\Controllers\Controller;
use App\Http\Resources\Business\PayFacOwnerResource;
use App\Http\Requests\Business\StorePayFacOwnerRequest;
use App\Http\Requests\Business\UpdatePayFacOwnerRequest;

class PayFacOwnerController extends Controller {

	public function __construct() {
  	$this->middleware('auth:business');
  }

  public function store(StorePayFacOwnerRequest $request) {
  	$business = Business::getAuthBusiness();
  	$business->account->payFacAccount->storeData($request->validated(), $type = 'owner');
  	return PayFacOwnerResource::collection($business->fresh()->account->getPayFacOwners());
  }

  public function update(UpdatePayFacOwnerRequest $request, PayFacOwner $payFacOwner) {
  	$business = Business::getAuthBusiness();
    if ($payFacOwner->getOwningBusiness()->id != $business->id) {
      return response()->json(['errors' => 'Permission denied.'], 403);
    }
    $payFacOwner->updateData($request->validated());
    return PayFacOwnerResource::collection($business->fresh()->account->getPayFacOwners());
  }
}
