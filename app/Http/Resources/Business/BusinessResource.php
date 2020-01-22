<?php

namespace App\Http\Resources\Business;

use App\Models\Business\Business;
use Illuminate\Http\Resources\Json\JsonResource;

class BusinessResource extends JsonResource
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
            'email' => $this->email
        ];
    }
}
