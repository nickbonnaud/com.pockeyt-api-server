<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;

class StorePayFacBankRequest extends FormRequest
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
  public function rules() {
    return [
      'state' => 'required|string|alpha|size:2',
      'city' => 'required|string',
      'zip' => 'required|numeric|digits:5',
      'address' => 'required|string',
      'address_secondary' => 'string|nullable',
      'first_name' => 'required|string|min:3',
      'last_name' => 'required|string|min:3',
      'routing_number' => 'required|numeric|digits:9',
      'account_number' => 'required|numeric|digits_between:6,17',
      'account_type' => 'required|in:checking,savings'
    ];
  }
}
