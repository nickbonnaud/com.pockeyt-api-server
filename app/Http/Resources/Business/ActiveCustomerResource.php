<?php

namespace App\Http\Resources\Business;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Transaction\TransactionResource;
use App\Http\Resources\Business\CustomerResource;


class ActiveCustomerResource extends JsonResource {
  
  public function toArray($request) {
    return [
      'customer' => new CustomerResource($this->customer),
      'transaction' => new TransactionResource($this->transaction),
      'notification' => $this->notification,
      'entered_at' => $this->created_at->toDateTimeString()
    ];
  }
}
