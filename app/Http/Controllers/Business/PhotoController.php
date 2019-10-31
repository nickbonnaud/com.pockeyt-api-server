<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business\Business;
use App\Models\Business\Profile;
use App\Models\Business\Photo;
use App\Http\Requests\Business\StorePhotoRequest;
use App\Http\Resources\Business\ProfilePhotosResource;

class PhotoController extends Controller {

  public function __construct() {
  	$this->middleware('auth:business');
  }

  public function store(Profile $profile, StorePhotoRequest $request) {
  	if ($profile->id != (Business::getAuthBusiness())->profile->id) {
  		return response()->json(['errors' => 'Permission denied.'], 403);
  	}

  	$photos = (new Photo)->createPhoto($request->validated(), $profile);
  	return new ProfilePhotosResource($photos);
  }
}
