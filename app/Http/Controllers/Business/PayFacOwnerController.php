<?php

namespace App\Http\Controllers\Business;

use Illuminate\Http\Request;
use App\Models\Business\Business;
use App\Models\Business\PayFacOwner;
use App\Http\Controllers\Controller;
use App\Http\Resources\Business\PayFacOwnerResource;
use App\Http\Requests\Business\StorePayFacOwnerRequest;
use App\Http\Requests\Business\UpdatePayFacOwnerRequest;

class PayFacOwnerController extends Controller {

	public function __construct() {
  	$this->middleware('auth:business');
    $this->middleware('csrf');
  }

  public function store(StorePayFacOwnerRequest $request) {
  	$business = Business::getAuthBusiness();
  	$owner = $business->account->payFacAccount->storeData($request->validated(), $type = 'owner');
  	return new PayFacOwnerResource($owner);
  }

  public function update(UpdatePayFacOwnerRequest $request, PayFacOwner $payFacOwner) {
  	$business = Business::getAuthBusiness();
    if ($payFacOwner->getOwningBusiness()->id != $business->id) {
      return response()->json(['errors' => 'Permission denied.'], 403);
    }
    
    $payFacOwner->updateData($request->validated());
    return new PayFacOwnerResource($payFacOwner->fresh());
  }

  public function destroy(Request $request, PayFacOwner $payFacOwner) {
    $business = Business::getAuthBusiness();
    if ($payFacOwner->getOwningBusiness()->id != $business->id) {
      return response()->json(['errors' => 'Permission denied.'], 403);
    }

    if ($payFacOwner->primary) {
      return response()->json(['errors' => 'Cannot delete primary owner.'], 403);
    }
    $payFacOwner->delete();
    return response()->json(['data' => ['success' => true]], 200);
  }
}
