<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StoreIssueRequest extends FormRequest {
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
    return [
      'transaction_identifier' => 'required|string|exists:transactions,identifier',
      'type' => 'required|string|in:wrong_bill,error_in_bill,other',
      'issue' => 'required|string|min:5'
    ];
  }
}
