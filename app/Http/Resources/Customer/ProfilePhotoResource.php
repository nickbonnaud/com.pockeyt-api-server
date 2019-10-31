<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfilePhotoResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function toArray($request) {
    return [
      'name' => $this->avatar->name,
      'small_url' => $this->avatar->small_url,
      'large_url' => $this->avatar->large_url
    ];
  }
}
