<?php

namespace App\Filters;

class BusinessFilters extends Filters {

	protected $filters = ['name', 'beacon'];

	protected function name($value) {
		return $this->builder->whereHas('profile', function($q) use ($value) {
			$q->where('name', 'LIKE', "%{$value}%");
		});
	}

	protected function beacon($value) {
		return $this->builder->whereHas('location.beaconAccount', function($q) use ($value) {
			$q->where('identifier', $value);
		});
	}
}