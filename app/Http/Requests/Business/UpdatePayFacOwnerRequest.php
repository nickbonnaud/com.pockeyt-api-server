<?php

namespace App\Http\Requests\Business;

use App\Rules\PercentOwnership;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePayFacOwnerRequest extends FormRequest
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
      'dob' => 'required|date',
      'ssn' => ['required', 'string', 'regex:/^(?!000|666)[X0-9]{3}([ -]?)(?!00)[X0-9]{2}\1(?!0000)[0-9]{4}$/'],
      'last_name' => 'required|string',
      'first_name' => 'required|string',
      'title' => 'required|string',
      'phone' => ['required', 'string', 'regex:/^(\+0?1\s)?\(?\d{3}\)?[\s.-]\d{3}[\s.-]?\d{4}$/'],
      'email' => 'required|email',
      'primary' => 'required|boolean',
      'percent_ownership' => ['required', 'integer', new PercentOwnership($this->pay_fac_owner)],
    ];
  }
}
