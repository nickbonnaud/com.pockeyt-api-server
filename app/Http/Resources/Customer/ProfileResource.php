<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Customer\ProfileResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $profile = parent::toArray($request);
        $profile['photos'] = new ProfilePhotoResource($this->photo);
        return $profile;
    }
}
