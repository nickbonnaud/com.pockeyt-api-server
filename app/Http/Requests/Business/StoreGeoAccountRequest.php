<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;

class StoreGeoAccountRequest extends FormRequest
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
      'radius' => 'required|numeric|min:50|max:200'
    ];
  }
}
