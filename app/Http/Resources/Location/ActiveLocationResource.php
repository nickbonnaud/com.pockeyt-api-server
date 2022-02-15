<?php

namespace App\Http\Resources\Location;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Customer\BusinessResource;

class ActiveLocationResource extends JsonResource
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
        'business' => new BusinessResource($this->location->business),
        'transaction_id' => optional($this->transaction)->identifier,
        'last_notification' => optional($this->notification)->last
      ];
    }
}
