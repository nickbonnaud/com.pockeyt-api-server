<?php

namespace App\Http\Resources\Transaction;

use Illuminate\Http\Resources\Json\JsonResource;

class RefundResource extends JsonResource {
  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function toArray($request)
  {
    return [
      'total' => $this->total,
      'status' => $this->status->name,
      'created_at' => $this->created_at->toDateTimeString()
    ];
  }
}
