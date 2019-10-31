<?php

namespace App\Http\Resources\Business;

use Illuminate\Http\Resources\Json\JsonResource;

class GeoAccountResource extends JsonResource
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
            'lat' => $this->lat,
            'lng' => $this->lng,
            'radius' => $this->radius
        ];
    }
}
