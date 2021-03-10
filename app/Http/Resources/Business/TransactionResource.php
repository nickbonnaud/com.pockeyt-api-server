<?php

namespace App\Http\Resources\Business;

use Illuminate\Support\Arr;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Business\CustomerResource;
use App\Http\Resources\Business\EmployeeResource;
use App\Http\Resources\Transaction\PurchasedItemResource;
use App\Http\Resources\Transaction\RefundResource;


class TransactionResource extends JsonResource {
  
  public function toArray($request) {
  	$transaction =  parent::toArray($request);
    $transaction['status'] = $this->status;
    $transaction = Arr::except($transaction, ['employee_id', 'refunds', 'purchased_items']);
    return [
      'transaction' => $transaction,
      'customer' => new CustomerResource($this->customer),
      'employee' => new EmployeeResource($this->employee),
      'refunds' => RefundResource::collection($this->refunds),
      'purchased_items' => PurchasedItemResource::collection($this->formattedPurchashedItems()),
      'issue' => $this->issue
    ];
  }
}
