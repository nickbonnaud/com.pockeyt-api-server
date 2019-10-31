<?php

namespace App\Http\Resources\Business;

use Illuminate\Http\Resources\Json\JsonResource;

class PosAccountResource extends JsonResource
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
            'type' => $this->type,
            'takes_tips' => $this->takes_tips,
            'allows_open_tickets' => $this->allows_open_tickets,
            'status' => $this->status->name
        ];
    }
}
