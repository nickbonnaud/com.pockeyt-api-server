<?php

namespace App\Filters;

class TransactionFilters extends Filters {

	protected $filters = ['recent', 'status', 'customer', 'employee', 'date', 'customerFirst', 'customerLast', 'id'];

	protected function recent($value) {
		return $this->builder;
	}

	protected function status($value) {
		return $this->builder->whereHas('status', function($q) use ($value) {
			$q->where('code', $value);
		});
	}

	protected function customer($value) {
		return $this->builder->whereHas('customer', function($q) use ($value) {
			$q->where('identifier', $value);
		}); 
	}

	protected function employee($value) {
		return $this->builder->where('employee_id', $value);
	}

	protected function date($valueRange) {
		return $this->builder->whereBetween('created_at', [$valueRange[0], $valueRange[1]]);
	}

	protected function customerFirst($firstName) {
		return $this->builder->whereHas('customer.profile', function($q) use ($firstName) {
			$q->where('first_name', $firstName);
		}); 
	}

	protected function customerLast($lastName) {
		return $this->builder->whereHas('customer.profile', function($q) use ($lastName) {
			$q->where('last_name', $lastName);
		}); 
	}

	protected function id($identifier) {
		return $this->builder->where('identifier', $identifier); 
	}
}