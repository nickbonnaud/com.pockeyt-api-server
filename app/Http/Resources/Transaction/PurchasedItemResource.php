<?php

namespace App\Http\Resources\Transaction;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchasedItemResource extends JsonResource {
  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function toArray($request) {
    return [
      'name' => $this->name,
      'sub_name' => $this->sub_name,
      'price' => (int)$this->price,
      'main_id' => $this->main_id,
      'sub_id' => $this->sub_id,
      'quantity' => (int)$this->quantity,
      'total' => $this->quantity * $this->price
    ];
  }
}
