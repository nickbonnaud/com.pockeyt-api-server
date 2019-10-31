<?php

namespace App\Filters;

class CustomerLocationFilters extends Filters {

	protected $filters = ['status', 'withTransaction', 'date'];

	protected function status($value) {
		return $this->builder;
	}

	protected function withTransaction($value) {
		if ($value == 'true') {
			return $this->builder->whereNotNull('transaction_id');
		} elseif ($value == 'false') {
			return $this->builder->whereNull('transaction_id');
		}
	}

	protected function date($valueRange) {
		return $this->builder->whereBetween('created_at', [$valueRange[0], $valueRange[1]]);
	}
}