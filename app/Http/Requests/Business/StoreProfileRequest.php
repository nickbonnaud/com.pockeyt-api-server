<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;

class StoreProfileRequest extends FormRequest {
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
      'name' => 'required|min:2',
      'website' => 'required|url',
      'description' => 'required|min:25',
      'phone' => ['required', 'string', 'numeric', 'digits:10'],
      'google_place_id' => 'string|nullable',
      'hours' => 'required|array'
    ];
  }
}
