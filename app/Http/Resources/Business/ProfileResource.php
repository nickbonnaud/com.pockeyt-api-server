<?php

namespace App\Http\Resources\Business;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
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
      'name' => $this->name,
      'website' => $this->website,
      'description' => $this->description,
      'google_place_id' => $this->google_place_id
    ];
  }
}
