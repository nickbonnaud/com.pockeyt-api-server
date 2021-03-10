<?php

namespace App\Filters;

class MessageFilters extends Filters {

  protected $filters = ['unread'];

  protected function unread() {
    return $this->builder->where('sent_by_business', false)->where('read', false)
      ->orWhere(function($query) {
        $query->where('unread_reply', true)
        ->whereHas('replies', function($q) {
          $q->where('sent_by_business', false)
            ->where('read', false);
        });
    });
  }
}