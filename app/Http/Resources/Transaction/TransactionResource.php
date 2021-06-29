<?php

namespace App\Http\Resources\Transaction;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Transaction\PurchasedItemResource;
use App\Http\Resources\Transaction\RefundResource;

class TransactionResource extends JsonResource {
  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function toArray($request) {
    $transaction =  parent::toArray($request);
    $transaction['purchased_items'] = PurchasedItemResource::collection($this->purchasedItems);
    $transaction['refunds'] = RefundResource::collection($this->refunds);
    return $transaction;
  }
}
