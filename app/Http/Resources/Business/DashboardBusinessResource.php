<?php

namespace App\Http\Resources\Business;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Business\ProfileResource;
use App\Http\Resources\Business\ProfilePhotosResource;
use App\Http\Resources\Business\PayFacBusinessResource;
use App\Http\Resources\Business\PayFacOwnerResource;
use App\Http\Resources\Business\PayFacBankResource;
use App\Http\Resources\Business\GeoAccountResource;
use App\Http\Resources\Business\PosAccountResource;

class DashboardBusinessResource extends JsonResource {
  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function toArray($request) {
    return [
      'identifier' => $this->identifier,
      'email' => $this->email,
      'profile' => new ProfileResource($this->profile),
      'photos' => new ProfilePhotosResource($this->profile->photos ?? null),
      'accounts' => [
        'business_account' => new PayFacBusinessResource($this->account->payFacAccount->payFacBusiness ?? null),
        'owner_accounts' => $this->account->payFacAccount->payFacOwners ?? null != null ? PayFacOwnerResource::collection($this->account->payFacAccount->payFacOwners) : [],
        'bank_account' => new PayFacBankResource($this->account->payFacAccount->payFacBank ?? null),
        'account_status' => $this->account->status,
      ],
      'location' => new GeoAccountResource($this->location->geoAccount ?? null),
      'pos_account' => new PosAccountResource($this->posAccount)
    ];
  }
}
