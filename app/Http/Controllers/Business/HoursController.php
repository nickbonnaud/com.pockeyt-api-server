<?php

namespace App\Http\Controllers\Business;

use App\Models\Business\Business;
use App\Models\Business\Hours;
use App\Http\Controllers\Controller;
use App\Http\Resources\Business\HoursResource;
use App\Http\Requests\Business\StoreHoursRequest;
use App\Http\Requests\Business\UpdateHoursRequest;

class HoursController extends Controller {

	public function __construct() {
  	$this->middleware('auth:business');
    $this->middleware('csrf');
  }

  public function store(StoreHoursRequest $request) {
  	$business = Business::getAuthBusiness();
  	$hours = $business->profile->storeHours(new Hours($request->validated()));
  	return new HoursResource($hours);
  }

	public function update(UpdateHoursRequest $request, Hours $hours) {
		$business = Business::getAuthBusiness();
		if ($hours->profile->id != $business->profile->id) {
			return response()->json(['errors' => 'Permission denied.'], 403);
		}
		$hours = $hours->profile->updateHours($request->validated());
		return new HoursResource($hours);
	}
}
