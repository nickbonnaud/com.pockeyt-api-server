<?php

namespace App\Http\Resources\Business;

use App\Http\Resources\Business\HoursResource;
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
      'phone' => $this->phone,
      'hours' => new HoursResource($this->hours)
    ];
  }
}
