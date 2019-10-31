<?php

namespace App\Filters;

class TipFilters extends Filters {

	protected $filters = ['date', 'employees'];

	protected function date($valueRange) {
		return $this->builder->whereBetween('transactions.created_at', [$valueRange[0], $valueRange[1]]);
	}

	protected function employees($value) {
		return $this->builder->join('employees', 'transactions.employee_id', '=', 'employees.external_id')
      ->groupBy('employees.id')
      ->selectRaw('sum(tip) as tips, employees.first_name, employees.last_name');
	}
}