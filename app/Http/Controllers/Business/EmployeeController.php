<?php

namespace App\Http\Controllers\Business;

use App\Models\Business\Business;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\Http\Resources\Business\EmployeeResource;

class EmployeeController extends Controller {

	public function __construct() {
  	$this->middleware('auth:business');
  }

  public function index() {
  	$business = Business::getAuthBusiness();
  	return EmployeeResource::collection($business->employees()->paginate()->appends(Input::except('page')));
  }
}
