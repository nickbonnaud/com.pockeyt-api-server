<?php

namespace App\Http\Controllers\Business;

use App\Models\Business\Business;
use App\Http\Controllers\Controller;
use App\Models\Business\LoyaltyProgram;
use App\Http\Resources\Business\LoyaltyProgramResource;
use App\Http\Requests\Business\StoreLoyaltyProgramRequest;

class LoyaltyProgramController extends Controller {
  
  public function __construct() {
  	$this->middleware('auth:business');
  }

  public function index() {
  	$loyaltyProgram = (Business::getAuthBusiness())->loyaltyProgram;
  	return new LoyaltyProgramResource($loyaltyProgram);
  }

  public function store(StoreLoyaltyProgramRequest $request) {
  	$business = Business::getAuthBusiness();
  	$loyaltyProgram = $business->storeLoyaltyProgram(new LoyaltyProgram($request->validated()));
  	return new LoyaltyProgramResource($loyaltyProgram);
  }

  public function destroy(LoyaltyProgram $loyaltyProgram) {
  	if ((Business::getAuthBusiness())->loyaltyProgram->id != $loyaltyProgram->id) {
  		return response()->json(['errors' => 'Permission denied.'], 403);
  	}
  	$loyaltyProgram->destroyProgram();
  	return response()->json(['success' => 'Loyalty program deleted.'], 200);
  }
}
