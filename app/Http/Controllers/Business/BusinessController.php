<?php

namespace App\Http\Controllers\Business;

use App\Models\Business\Business;
use App\Http\Controllers\Controller;
use App\Http\Resources\Business\BusinessResource;
use App\Http\Requests\Business\UpdateBusinessRequest;

class BusinessController extends Controller
{
  public function __construct() {
  	$this->middleware('auth:business');
    $this->middleware('csrf');
  }

  public function index() {
  	$business = Business::getAuthBusiness();
  	$business['token'] = Business::refreshToken($business);
  	return new BusinessResource($business);
  }

  public function update(Business $business, UpdateBusinessRequest $request) {
  	if ($business->id != (Business::getAuthBusiness())->id) {
      return response()->json(['errors' => 'Permission denied.'], 403);
    }
  	$business->update($request->validated());
  	return new BusinessResource($business);
  }
}
