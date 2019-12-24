<?php

namespace App\Http\Resources\Business;

use Illuminate\Http\Resources\Json\JsonResource;

class PayFacOwnerResource extends JsonResource
{
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
      'dob' => $this->dob->format('m/d/Y'),
      'ssn' => str_repeat('X', strlen($this->ssn) - 6) . substr($this->ssn, -4),
      'last_name' => $this->last_name,
      'first_name' => $this->first_name,
      'title' => $this->title,
      'phone' => preg_replace("/[^0-9]/", "", $this->phone),
      'email' => $this->email,
      'primary' => $this->primary,
      'percent_ownership' => $this->percent_ownership
    ];
  }
}
