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
          if ($this->input('is_logo') == 'true') {
            if ($width < 150 || $height < 150) {
              return $fail('Logo must be larger than 150x150 pixels.');
            }
          } else {
            if ($width < 320 || $height < 100) {
              return $fail('Banner must be larger than 320x100 pixels.');
            }
          }
        }
      }],
      'is_logo' =>'required|boolean'
    ];
  }
}
