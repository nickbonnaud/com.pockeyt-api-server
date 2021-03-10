<?php

namespace App\Http\Requests\Business;

use Illuminate\Support\Str;
use Illuminate\Foundation\Http\FormRequest;

class UpdateHoursRequest extends FormRequest {
	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize() {
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules() {
		$rules = ['required', 'string', function ($attribute, $value, $fail) {
			if ($value != 'closed') {
				if (Str::length($value) < 17) {
					return $fail("The " . $attribute . " must be at least 17 characters.");
				}
			}
		}];

		return [
			'sunday' => $rules,
			'monday' => $rules,
			'tuesday' => $rules,
			'wednesday' => $rules,
			'thursday' => $rules,
			'friday' => $rules,
			'saturday' => $rules,
		];
	}
}
