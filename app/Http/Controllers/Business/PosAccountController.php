<?php

namespace App\Http\Controllers\Business;

use App\Models\Business\Business;
use App\Models\Business\PosAccount;
use App\Http\Controllers\Controller;
use App\Http\Resources\Business\PosAccountResource;
use App\Http\Requests\Business\StorePosAccountRequest;

class PosAccountController extends Controller {
  
  public function __construct() {
  	$this->middleware('auth:business');
  }

  public function index() {
  	$posAccount = (Business::getAuthBusiness())->posAccount;
  	return new PosAccountResource($posAccount);
  }

  public function store(StorePosAccountRequest $request) {
  	$posAccount = PosAccount::createAccount(Business::getAuthBusiness(), $request->validated());
  	return new PosAccountResource($posAccount);
  }

  public function update(StorePosAccountRequest $request, PosAccount $posAccount) {
    $business = Business::getAuthBusiness();
    if ($posAccount->business->id !== $business->id) {
      return response()->json(['errors' => 'Permission denied.'], 403);
    }

    $posAccount->updateAccount($request->validated());
    return new PosAccountResource($posAccount);
  }
}
