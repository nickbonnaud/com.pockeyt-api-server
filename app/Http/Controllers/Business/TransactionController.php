<?php

namespace App\Http\Controllers\Business;

use Illuminate\Http\Request;
use App\Models\Business\Business;
use App\Filters\TransactionFilters;
use App\Http\Controllers\Controller;
use App\Models\Transaction\Transaction;
use App\Http\Resources\Business\TransactionResource;

class TransactionController extends Controller {

	public function __construct() {
  	$this->middleware('auth:business');
    $this->middleware('csrf');
  }

  public function index(Request $request, TransactionFilters $filters) {
  	$business = Business::getAuthBusiness();
  	$query = Transaction::filter($filters)
      ->where('business_id', $business->id)
      ->orderBy('created_at', 'desc');
  	if ($request->has('sum')) {
  		return response()->json(['data' => ['sales_data' => (int) $query->sum($request->query('sum'))]]);
  	} elseif($request->has('count')) {
      return response()->json(['data' => ['sales_data' => (int) $query->count($request->query('count'))]]);
    }
  	return TransactionResource::collection($query->paginate(10)->appends($request->except('page')));
  }
}
