<?php

namespace App\Http\Resources\Customer;

use App\Models\Customer\Customer;
use App\Http\Resources\Customer\ProfileResource;
use App\Http\Resources\Customer\CustomerAccountResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function toArray($request)
  {
    return [
      'identifier' => $this->identifier,
      'email' => $this->email,
      'token' => $this->token,
      'profile' => new ProfileResource($this->profile),
      'account' => new CustomerAccountResource($this->account),
      'status' => $this->status
    ];
  }
}
