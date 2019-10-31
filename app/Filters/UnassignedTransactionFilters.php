<?php

namespace App\Filters;

class UnassignedTransactionFilters extends Filters {

	protected $filters = ['recent', 'date', 'employee'];

	protected function recent($value) {
		return $this->builder;
	}

	protected function date($valueRange) {
		return $this->builder->whereBetween('created_at', [$valueRange[0], $valueRange[1]]);
	}

	protected function employee($externalId) {
		return $this->builder->where('employee_id', $externalId);
	}
}