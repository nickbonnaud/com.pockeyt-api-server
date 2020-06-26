<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer\Customer;
use App\Models\Transaction\Transaction;
use App\Models\Transaction\TransactionIssue;
use App\Http\Requests\Customer\StoreIssueRequest;
use App\Http\Requests\Customer\UpdateIssueRequest;
use App\Http\Resources\Customer\TransactionResource;

class TransactionIssueController extends Controller {
  public function __construct() {
  	$this->middleware('auth:customer');
  }

  public function store(StoreIssueRequest $request) {
  	$customer = Customer::getAuthCustomer();
  	$transaction = Transaction::where('identifier', $request->transaction_identifier)->first();
    if ($customer->id != $transaction->customer_id) {
      return response()->json(['errors' => 'Permission denied.'], 403); 
    }

    $transaction = $transaction->createIssue($request->except('transaction_identifier'));
    return new TransactionResource($transaction);
  }

  public function update(TransactionIssue $transactionIssue, UpdateIssueRequest $request) {
  	$customer = Customer::getAuthCustomer();
  	if ($customer->id != $transactionIssue->transaction->customer_id) {
      return response()->json(['errors' => 'Permission denied.'], 403); 
    }

    $transaction = $transactionIssue->transaction->updateIssue($request->validated());
    return new TransactionResource($transaction);
  }

  public function destroy(TransactionIssue $transactionIssue) {
  	$customer = Customer::getAuthCustomer();
  	if ($customer->id != $transactionIssue->transaction->customer_id) {
      return response()->json(['errors' => 'Permission denied.'], 403); 
    }

    $transaction = $transactionIssue->transaction->deleteIssue();
    return new TransactionResource($transaction);
  }
}
