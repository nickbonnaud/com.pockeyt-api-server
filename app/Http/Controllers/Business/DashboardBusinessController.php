<?php

namespace App\Http\Controllers\Business;

use App\Models\Business\Business;
use App\Http\Controllers\Controller;
use App\Http\Resources\Business\DashboardBusinessResource;

class DashboardBusinessController extends Controller {
  
  public function __construct() {
  	$this->middleware('auth:business');
  }


  public function index() {
  	$business = Business::getAuthBusiness();
  	return new DashboardBusinessResource($business);
  }
}
