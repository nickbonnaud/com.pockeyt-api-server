<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Filters\BusinessFilters;
use App\Models\Business\Business;
use App\Http\Resources\Customer\BusinessResource;

class BusinessController extends Controller {

	public function __construct() {
  	$this->middleware('auth:customer');
  }

  public function index(Request $request, BusinessFilters $filters) {
  	$businesses = Business::filter($filters)->get();
  	return BusinessResource::collection($businesses);
  }
}
