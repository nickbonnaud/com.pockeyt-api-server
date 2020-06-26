<?php

namespace App\Http\Resources\Customer;

use Illuminate\Support\Arr;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Customer\PurchasedItemResource;
use App\Http\Resources\Customer\BusinessResource;

class UnassignedTransactionResource extends JsonResource {

  public function toArray($request) {
    $transaction = parent::toArray($request);
    $transaction['purchased_items'] = PurchasedItemResource::collection($this->formattedPurchashedItems());

     return [
      'transaction' => Arr::except($transaction, ['customer_id', 'business_id', 'status_id', 'pos_transaction_id']),
      'business' => new BusinessResource($this->business),
    ];
  }
}
