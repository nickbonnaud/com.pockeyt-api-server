<?php

namespace App\Http\Controllers\Business;

use Illuminate\Http\Request;
use App\Models\Business\Business;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\Filters\UnassignedTransactionFilters;
use App\Models\Transaction\UnassignedTransaction;
use App\Http\Resources\Business\UnassignedTransactionResource;

class UnassignedTransactionController extends Controller {
  
  public function __construct() {
  	$this->middleware('auth:business');
  }

  public function index(Request $request, UnassignedTransactionFilters $filters) {
  	$business = Business::getAuthBusiness();
  	$query = UnassignedTransaction::filter($filters)
      ->where('business_id', $business->id)
      ->orderBy('created_at', 'desc');
  	return UnassignedTransactionResource::collection($query->paginate(10)->appends(Input::except('page')));
  }
}
