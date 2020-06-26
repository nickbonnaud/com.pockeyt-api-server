<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Business\ProfilePhotosResource;
use App\Http\Resources\Business\GeoAccountResource;
use App\Http\Resources\Business\BeaconResource;

class BusinessResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function toArray($request) {
    $business = $this->business == null ? $this : $this->business;
    return [
      'identifier' => $business->identifier,
      'profile' => [
      	'name' => $business->profile->name,
      	'website' => $business->profile->website,
      	'description' => $business->profile->description,
      	'google_place_id' => $business->profile->google_place_id,
        'phone' => $business->profile->phone,
        'hours' => $business->profile->hours
      ],
      'photos' => new ProfilePhotosResource($business->profile->photos),
      'location' => [
      	'geo' => new GeoAccountResource($business->location->geoAccount),
      	'beacon' => new BeaconResource($business->location->beaconAccount),
        'region' => $business->location->region
      ],
    ];
  }
}
