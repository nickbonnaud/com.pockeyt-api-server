<?php

namespace App\Http\Resources\Business;

use Illuminate\Http\Resources\Json\JsonResource;

class PayFacBusinessResource extends JsonResource
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
    	'ein' => $this->ein,
    	'business_name' => $this->business_name,
      'address' => [
        'address' => $this->address,
        'address_secondary' => $this->address_secondary,
        'city' => $this->city,
        'state' => $this->state,
        'zip' => $this->zip,
      ],
    	'entity_type' => $this->payFacAccount->entity_type
    ];
  }
}
