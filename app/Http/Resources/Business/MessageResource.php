<?php

namespace App\Http\Resources\Business;

use App\Http\Resources\Business\ReplyResource;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource {
  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array
   */
  public function toArray($request) {
    $message =  parent::toArray($request);
    $message['replies'] = ReplyResource::collection($this->replies()->orderBy('created_at', 'desc')->get());
    return $message;
  }
}
