<?php

namespace App\Http\Resources\Business;

use Illuminate\Http\Resources\Json\JsonResource;

class TipsResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function toArray($request) {
    return [
      'first_name' => $this->first_name,
      'last_name' => $this->last_name,
      'tips' => (int) $this->tips,
    ];
  }
}
