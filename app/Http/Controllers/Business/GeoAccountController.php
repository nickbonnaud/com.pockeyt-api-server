<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business\Location;
use App\Models\Business\Business;
use App\Models\Business\GeoAccount;
use App\Http\Resources\Business\GeoAccountResource;
use App\Http\Requests\Business\StoreGeoAccountRequest;

class GeoAccountController extends Controller {

  public function __construct() {
  	$this->middleware('auth:business');
    $this->middleware('csrf');
  }

  public function store(StoreGeoAccountRequest $request) {
    $business = Business::getAuthBusiness();
    $location = Location::createLocation($request->only(['lat', 'lng']), $business);
    $geoAccount = new GeoAccount($request->validated());
    $location->geoAccount()->save($geoAccount);
    return new GeoAccountResource($geoAccount);
  }

  public function update(GeoAccount $geoAccount, StoreGeoAccountRequest $request) {
  	if ($geoAccount->id != (Business::getAuthBusiness())->location->geoAccount->id) {
  		return response()->json(['errors' => 'Permission denied.'], 403);
  	}
  	$geoAccount->updateAccount($request->validated());
  	return new GeoAccountResource($geoAccount);
  }
}
