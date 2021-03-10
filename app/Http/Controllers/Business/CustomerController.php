<?php

namespace App\Http\Controllers\Business;

use Illuminate\Http\Request;
use App\Models\Business\Business;
use App\Http\Controllers\Controller;
use App\Models\Location\ActiveLocation;
use App\Filters\CustomerLocationFilters;
use App\Models\Location\HistoricLocation;
use App\Http\Resources\Business\ActiveCustomerResource;

class CustomerController extends Controller {

	public function __construct() {
  	$this->middleware('auth:business');
		$this->middleware('csrf');
  }

  public function index(Request $request, CustomerLocationFilters $filters) {
  	$business = Business::getAuthBusiness();
  	$query = $request->query('status') == 'active' ? ActiveLocation::filter($filters) : HistoricLocation::filter($filters);
  	$query->where('location_id', $business->location->id)->orderBy('created_at', 'desc');
  	return ActiveCustomerResource::collection($query->paginate()->appends($request->except('page')));
  }
}
