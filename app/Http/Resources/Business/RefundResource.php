<?php

namespace App\Http\Resources\Business;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Transaction\RefundResource as TransactionRefund;
use App\Http\Resources\Business\TransactionResource;

class RefundResource extends JsonResource {

  public function toArray($request) {
    return [
      'refund' => new TransactionRefund($this),
      'transaction_resource' => new TransactionResource($this->transaction),
    ];
  }
}
