<?php

namespace App\Http\Resources\Customer;

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
      'price' => $this->price,
      'quantity' => $this->quantity,
      'total' => $this->quantity * $this->price
    ];
  }
}
