<?php

namespace App\Filters;

class HelpTicketFilters extends Filters {

	protected $filters = ['resolved'];

	protected function resolved($value) {
		return $this->builder->where('resolved', filter_var($value, FILTER_VALIDATE_BOOLEAN));
	}
}