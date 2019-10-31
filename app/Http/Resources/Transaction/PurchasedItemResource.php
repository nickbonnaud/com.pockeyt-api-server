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
    $item = $this->getInventoryItem();
    return [
      'name' => $item->name,
      'sub_name' => $item->sub_name,
      'price' => $item->price
    ];
  }
}
