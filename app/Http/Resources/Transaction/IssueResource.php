<?php

namespace App\Http\Resources\Transaction;

use Illuminate\Http\Resources\Json\JsonResource;

class IssueResource extends JsonResource {
  public function toArray($request){
    $issue =  parent::toArray($request);
    $issue['warnings_sent'] = !is_null($this->transaction->notification)
      ? $this->transaction->notification->number_times_fix_bill_sent
      : 0;
    return $issue; 
  }
}
