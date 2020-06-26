<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Customer\PurchasedItemResource;
use App\Http\Resources\Transaction\RefundResource;
use App\Http\Resources\Customer\BusinessResource;

class TransactionResource extends JsonResource {
  public function toArray($request) {
    $transaction = parent::toArray($request);
    $transaction['status'] = $this->status;
    $transaction['purchased_items'] = PurchasedItemResource::collection($this->formattedPurchashedItems());
    return [
      'transaction' => $transaction,
      'business' => new BusinessResource($this->business),
      'refund' => RefundResource::collection($this->refunds),
      'issue' => $this->issue
    ];
  }
}
