<?php

namespace App\Http\Controllers\Business;

use App\Filters\TipFilters;
use Illuminate\Http\Request;
use App\Models\Business\Business;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use App\Models\Transaction\Transaction;
use App\Http\Resources\Business\TipsResource;

class TipController extends Controller {

	public function __construct() {
  	$this->middleware('auth:business');
  }

  public function index(Request $request, TipFilters $filters) {
    $business = Business::getAuthBusiness();
    $query = Transaction::filter($filters)->where('transactions.business_id', $business->id);
    return TipsResource::collection($query->paginate()->appends(Input::except('page')));
  }
}
