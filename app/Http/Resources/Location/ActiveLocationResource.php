<?php

namespace App\Http\Resources\Location;

use Illuminate\Http\Resources\Json\JsonResource;

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
        'active_location_id' => $this->identifier,
        'beacon_identifier' => $this->location->beaconAccount->identifier,
        'transaction_id' => optional($this->transaction)->identifier,
        'last_notification' => optional($this->notification)->last
      ];
    }
}
