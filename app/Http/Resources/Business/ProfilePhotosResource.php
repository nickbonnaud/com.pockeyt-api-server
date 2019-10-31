<?php

namespace App\Http\Resources\Business;

use Illuminate\Http\Resources\Json\JsonResource;

class ProfilePhotosResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function toArray($request) {
    return [
      'logo' => [
        'name' => $this->logo ? $this->logo->name : null,
        'small_url' => $this->logo ? $this->logo->small_url : null,
        'large_url' => $this->logo ? $this->logo->large_url : null
      ],
      'banner' => [
        'name' => $this->banner ? $this->banner->name : null,
        'small_url' => $this->banner ? $this->banner->small_url : null,
        'large_url' => $this->banner ? $this->banner->large_url : null
      ]
    ];
  }
}
