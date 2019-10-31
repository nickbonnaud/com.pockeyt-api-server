<?php

namespace App\Filters;

class RefundFilters extends Filters {

	protected $filters = ['recent', 'date', 'firstName', 'lastName'];

	protected function recent($value) {
		return $this->builder;
	}

	protected function date($valueRange) {
		return $this->builder->whereBetween('created_at', [$valueRange[0], $valueRange[1]]);
	}

	protected function firstName($firstName) {
		return $this->builder->whereHas('transaction.customer.profile', function($q) use ($firstName) {
			$q->where('first_name', $firstName);
		});
	}

	protected function lastName($lastName) {
		return $this->builder->whereHas('transaction.customer.profile', function($q) use ($lastName) {
			$q->where('last_name', $lastName);
		});
	}
}