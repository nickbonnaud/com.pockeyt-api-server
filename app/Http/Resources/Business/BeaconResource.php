<?php

namespace App\Http\Resources\Business;

use Illuminate\Http\Resources\Json\JsonResource;

class BeaconResource extends JsonResource
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
      'region_identifier' => $this->location->region->identifier,
      'major' => $this->major,
      'minor' => $this->minor
    ];
  }
}
