<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Customer\TransactionResource;
use App\Http\Resources\Customer\BusinessResource;

class RefundResource extends JsonResource {
  
  public function toArray($request) {
    $refund = parent::toArray($request);
    $refund['status'] = $this->status->name;
    $transaction = $this->transaction->toArray();
    $transaction['status'] = $this->transaction->status->name;
    $transaction['purchased_items'] = PurchasedItemResource::collection($this->transaction->formattedPurchashedItems());
    return [
    	'refund'=> $refund,
    	'business' => new BusinessResource($this->transaction->business),
    	'transaction' => $transaction,
        'issue' => $this->transaction->issue
    ];
  }
}
