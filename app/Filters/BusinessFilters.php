<?php

namespace App\Filters;

class BusinessFilters extends Filters {

	protected $filters = ['name'];

	protected function name($value) {
		return $this->builder->whereHas('profile', function($q) use ($value) {
			$q->where('name', 'LIKE', "%{$value}%");
		});
	}
}