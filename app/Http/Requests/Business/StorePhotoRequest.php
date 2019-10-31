<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;


class StorePhotoRequest extends FormRequest
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
      'photo' => ['required', 'file', 'mimes:jpg,jpeg,png', function($attribute, $value, $fail) {
        if (is_file($value)) {
          list($width, $height) = getimagesize($value);
          if ($this->input('is_logo')) {
            if ($width < 400 || $height < 400) {
              return $fail('Logo must be larger than 400x400 pixels.');
            }
          } else {
            if ($width < 1000 || $height < 720) {
              return $fail('Banner must be larger than 1000x720 pixels.');
            }
          }
        }
      }],
      'is_logo' =>'required|boolean'
    ];
  }
}
