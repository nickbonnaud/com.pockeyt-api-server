<?php

namespace App\Http\Controllers\Business;

use Illuminate\Http\Request;
use App\Filters\RefundFilters;
use App\Models\Business\Business;
use App\Models\Refund\Refund;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\Http\Resources\Business\RefundResource;

class RefundController extends Controller {
  
  public function __construct() {
  	$this->middleware('auth:business');
  }

  public function index(Request $request, RefundFilters $filters) {
  	$business = Business::getAuthBusiness();
  	$query = Refund::filter($filters)
  		->whereHas('transaction', function($q) use ($business) {
  			$q->where('business_id', $business->id);
  		})->orderBy('created_at', 'desc');

    return RefundResource::collection($query->paginate(10)->appends(Input::except('page')));
  }
}
