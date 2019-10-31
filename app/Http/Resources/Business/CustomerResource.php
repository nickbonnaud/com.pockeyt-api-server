<?php

namespace App\Http\Resources\Business;

use Illuminate\Http\Resources\Json\JsonResource;


class CustomerResource extends JsonResource {
  
  public function toArray($request) {
    return [
      'identifier' => $this->identifier,
      'email' => $this->email,
      'first_name' =>  $this->profile->first_name,
      'last_name' => $this->profile->last_name,
      'photo' => $this->profile->photo->avatar,
    ];
  }
}
