<?php

namespace App\Filters;

class TransactionFilters extends Filters
{

	protected $filters = ['status', 'customer', 'employee', 'employeeFirst', 'employeeLast', 'date', 'customerFirst', 'customerLast', 'id', 'business', 'open', 'unique'];

	protected function status($value) {
		return $this->builder->whereHas('status', function ($q) use ($value) {
			$q->where('code', $value);
		});
	}

	protected function customer($value) {
		return $this->builder->whereHas('customer', function ($q) use ($value) {
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
		return $this->builder->whereHas('customer.profile', function ($q) use ($firstName) {
			$q->where('first_name', 'LIKE', "%{$firstName}%");
		});
	}

	protected function customerLast($lastName) {
		return $this->builder->whereHas('customer.profile', function ($q) use ($lastName) {
			$q->where('last_name', 'LIKE', "%{$lastName}%");
		});
	}

	protected function employeeFirst($firstName) {
		return $this->builder->whereHas('employee', function ($q) use ($firstName) {
			$q->where('first_name', 'LIKE', "%{$firstName}%");
		});
	}

	protected function employeeLast($lastName) {
		return $this->builder->whereHas('employee', function ($q) use ($lastName) {
			$q->where('last_name', 'LIKE', "%{$lastName}%");
		});
	}

	protected function id($identifier) {
		return $this->builder->where('identifier', $identifier);
	}

	protected function business($identifier) {
		return $this->builder->whereHas('business', function ($q) use ($identifier) {
			$q->where('identifier', $identifier);
		});
	}

	protected function open() {
		return $this->builder->whereHas('status', function ($q) {
			$q->whereNotIn('code', [200]);
		});
	}

	protected function unique($attribute) {
		return $this->builder->distinct($attribute);
	}
}
