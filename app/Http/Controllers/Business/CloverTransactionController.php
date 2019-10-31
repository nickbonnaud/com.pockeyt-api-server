<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Transaction\UnassignedTransaction;
use App\Models\Transaction\TransactionStatus;
use App\Models\Customer\Customer;
use App\Http\Requests\Business\UpdateCloverTransactionRequest;

class CloverTransactionController extends Controller {

	public function __construct() {
  	$this->middleware('auth:business');
  }

  public function update(UpdateCloverTransactionRequest $request) {
  	$unassignedTransaction = UnassignedTransaction::where('pos_transaction_id', $request->pos_transaction_id)->first();
  	$customer = Customer::getByIdentifier($request->customer_identifier);
  	$status = TransactionStatus::getByName($request->status_name);
  	$unassignedTransaction->assignCustomer($customer, $status);
  	return response()->json(['success' => 'Customer assigned'], 200);
  }
}
