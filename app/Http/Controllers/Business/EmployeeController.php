<?php

namespace App\Http\Controllers\Business;

use Illuminate\Http\Request;
use App\Models\Business\Business;
use App\Http\Controllers\Controller;
use App\Http\Resources\Business\EmployeeResource;

class EmployeeController extends Controller {

	public function __construct() {
  	$this->middleware('auth:business');
  }

  public function index(Request $request) {
  	$business = Business::getAuthBusiness();
  	return EmployeeResource::collection($business->employees()->paginate()->appends($request->except('page')));
  }
}
