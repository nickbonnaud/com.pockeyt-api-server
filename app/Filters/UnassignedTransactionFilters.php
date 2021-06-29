<?php

namespace App\Filters;

class UnassignedTransactionFilters extends Filters {

	protected $filters = ['date'];

	protected function date($valueRange) {
		return $this->builder->whereBetween('created_at', [$valueRange[0], $valueRange[1]]);
	}
}