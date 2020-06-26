<?php

namespace App\Http\Controllers\Customer;

use Illuminate\Http\Request;
use App\Models\Customer\Customer;
use App\Filters\TransactionFilters;
use App\Http\Controllers\Controller;
use App\Models\Transaction\Transaction;
use App\Http\Resources\Customer\TransactionResource;
use App\Http\Requests\Customer\UpdateTransactionRequest;

class TransactionController extends Controller {

	public function __construct() {
  	$this->middleware('auth:customer');
  }

  public function index(Request $request, TransactionFilters $filters) {
  	$customer = Customer::getAuthCustomer();
  	$query = Transaction::filter($filters)
  		->where('customer_id', $customer->id)
  		->orderBy('created_at', 'desc');

  	return TransactionResource::collection($query->paginate()->appends($request->except('page'))); 
  }

  public function update(Transaction $transaction, UpdateTransactionRequest $request) {
    $customer = Customer::getAuthCustomer();
    if ($customer->id != $transaction->customer_id) {
      return response()->json(['errors' => 'Permission denied.'], 403); 
    }

    $transaction = $transaction->updateStatus($request->status_code);
    return new TransactionResource($transaction->fresh());
  }
}
