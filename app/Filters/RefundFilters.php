<?php

namespace App\Filters;

class RefundFilters extends Filters {

	protected $filters = ['id', 'transactionId', 'recent', 'date', 'firstName', 'lastName', 'status', 'business'];

	protected function recent($value) {
		return $this->builder;
	}

	protected function id($identifier) {
		return $this->builder->where('identifier', $identifier);
	}

	protected function transactionId($identifier) {
		return $this->builder->whereHas('transaction', function($q) use ($identifier) {
			$q->where('identifier', $identifier);
		});
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

	protected function status($code) {
		return $this->builder->whereHas('status', function($q) use ($code) {
			$q->where('code', $code);
		});
	}

	protected function business($identifier) {
		return $this->builder->whereHas('transaction.business', function($q) use ($identifier) {
			$q->where('identifier', $identifier);
		});
	}
}