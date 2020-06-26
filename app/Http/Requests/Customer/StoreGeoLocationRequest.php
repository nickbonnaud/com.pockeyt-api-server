<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StoreGeoLocationRequest extends FormRequest
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
      'lat' => 'required|numeric|min:-90|max:90',
      'lng' => 'required|numeric|min:-180|max:180',
      'start_location' => 'required|boolean'
    ];
  }
}
