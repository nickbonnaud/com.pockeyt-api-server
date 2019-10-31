<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;

class StoreLoyaltyProgramRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   *
   * @return bool
   */
  public function authorize()
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array
   */
  public function rules()
  {
    return [
      'purchases_required' => ['required_without:amount_required', 'integer', 'min:0', function($attribute, $value, $fail) {
        if ($value && $this->input('amount_required')) return $fail('Can only have number of purchases requirement or total amount spent requirement.');
      }],
      'amount_required' => ['required_without:purchases_required', 'integer', 'min:0', function($attribute, $value, $fail) {
        if ($value && $this->input('purchases_required')) return $fail('Can only have number of purchases requirement or total amount spent requirement.');
      }],
      'reward' => 'required|string|min:2'
    ];
  }
}
