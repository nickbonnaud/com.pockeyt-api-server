<?php

namespace App\Http\Controllers\Customer;

use Illuminate\Http\Request;
use App\Models\Refund\Refund;
use App\Filters\RefundFilters;
use App\Models\Customer\Customer;
use App\Http\Controllers\Controller;
use App\Http\Resources\Customer\RefundResource;

class RefundController extends Controller {

	public function __construct() {
  	$this->middleware('auth:customer');
  }

  public function index(Request $request, RefundFilters $filters) {
  	$customer = Customer::getAuthCustomer();
  	$query = Refund::filter($filters)
  		->whereHas('transaction', function($q) use ($customer) {
  			$q->where('customer_id', $customer->id);
  		})->orderBy('created_at', 'desc');

  	return RefundResource::collection($query->paginate()->appends($request->except('page')));
  }
}
