<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Business\Business;
use App\Models\Customer\Customer;
use App\Models\Transaction\UnassignedTransaction;
use App\Models\Transaction\Transaction;
use App\Models\Transaction\TransactionStatus;
use App\Http\Resources\Customer\UnassignedTransactionResource;
use App\Http\Resources\Customer\TransactionResource;

class UnassignedTransactionController extends Controller {

	public function __construct() {
  	$this->middleware('auth:customer');
  }

  public function index(Request $request) {
  	$request->validate([
  		'business_id' => 'exists:businesses,identifier'
  	]);
  	$business = Business::where('identifier', $request->business_id)->first();
  	return UnassignedTransactionResource::collection(is_null($business) ? collect() : $business->unassignedTransactions);
  }

  public function update(UnassignedTransaction $unassignedTransaction, Request $request) {
  	$customer = Customer::getAuthCustomer();
  	$status = $unassignedTransaction->status != null ? $unassignedTransaction->status : TransactionStatus::where('code', 100)->first();
  	$unassignedTransaction->assignCustomer($customer, $status);
  	return new TransactionResource(Transaction::where('bill_created_at', $unassignedTransaction->created_at)->first());
  }
}
