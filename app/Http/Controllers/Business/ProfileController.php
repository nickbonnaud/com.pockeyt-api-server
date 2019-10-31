<?php

namespace App\Http\Controllers\Business;

use App\Models\Business\Profile;
use App\Models\Business\Business;
use App\Http\Controllers\Controller;
use App\Http\Resources\Business\ProfileResource;
use App\Http\Requests\Business\StoreProfileRequest;
use App\Http\Requests\Business\UpdateProfileRequest;

class ProfileController extends Controller {
  
  public function __construct() {
  	$this->middleware('auth:business');
  }

  public function index() {
  	$business = Business::getAuthBusiness();
  	return new ProfileResource($business->profile);
  }

  public function store(StoreProfileRequest $request) {
  	$business = Business::getAuthBusiness();
  	$profile = $business->storeProfile(new Profile($request->validated()));
  	return new ProfileResource($profile);
  }

  public function update(Profile $profile, UpdateProfileRequest $request) {
  	if ($profile->id != (Business::getAuthBusiness())->profile->id) {
  		return response()->json(['errors' => 'Permission denied.'], 403);
  	}
  	$profile->update($request->validated());
  	return new ProfileResource($profile);
  }
}
