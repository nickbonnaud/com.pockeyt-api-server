<?php

namespace App\Http\Resources\Business;

use Illuminate\Support\Arr;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Business\EmployeeResource;
use App\Http\Resources\Transaction\PurchasedItemResource;

class UnassignedTransactionResource extends JsonResource {

  public function toArray($request) {
    $unassignedTransaction = parent::toArray($request);
    $unassignedTransaction['purchased_items'] = PurchasedItemResource::collection($this->purchasedItems);
    return [
      'transaction' => Arr::except($unassignedTransaction, ['business_id', 'customer_id', 'status_id', 'pos_transaction_id', 'employee_id', 'partial_payment']),
      'employee' => new EmployeeResource($this->employee)
    ];
  }
}
