<?php

namespace App\Http\Resources\Business;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Business\CustomerResource;
use App\Http\Resources\Business\EmployeeResource;
use App\Http\Resources\Transaction\RefundResource as TransactionRefund;
use App\Http\Resources\Transaction\TransactionResource;
use Illuminate\Support\Arr;

class RefundResource extends JsonResource {

  public function toArray($request) {
    $customer = $this->transaction->customer;
    $employee = $this->transaction->employee;
    $transaction = new TransactionResource($this->transaction);
    $transaction = Arr::except($transaction, ['employee', 'customer']);
    return [
      'refund' => new TransactionRefund($this),
      'transaction' => $transaction,
      'customer' => new CustomerResource($customer),
      'employee' => new EmployeeResource($employee)
    ];
  }
}
