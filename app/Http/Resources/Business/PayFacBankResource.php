<?php

namespace App\Http\Resources\Business;

use Illuminate\Http\Resources\Json\JsonResource;

class PayFacBankResource extends JsonResource {
  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function toArray($request) {
    return [
      'identifier' => $this->identifier,
      'address' => [
        'address' => $this->address,
        'address_secondary' => $this->address_secondary,
        'city' => $this->city,
        'state' => $this->state,
        'zip' => $this->zip,
      ],
      'first_name' => $this->first_name,
      'last_name' => $this->last_name,
      'routing_number' => str_repeat('X', strlen($this->routing_number) - 4) . substr($this->routing_number, -4),
      'account_number' => str_repeat('X', strlen($this->account_number) - 4) . substr($this->account_number, -4),
      'account_type' => $this->account_type
    ];
  }
}
