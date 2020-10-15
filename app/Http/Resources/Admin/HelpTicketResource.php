<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Admin\HelpTicketReplyResource;

class HelpTicketResource extends JsonResource {
  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function toArray($request){
    $ticketResource = parent::toArray($request);
    $ticketResource['replies'] = HelpTicketReplyResource::collection($this->replies);
    return $ticketResource;
  }
}
