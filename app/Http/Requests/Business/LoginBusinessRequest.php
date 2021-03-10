<?php

namespace App\Http\Requests\Business;

use App\Models\Business\Business;
use Illuminate\Foundation\Http\FormRequest;

class LoginBusinessRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   *
   * @return bool
   */
  public function authorize() {
    $credentials = $this->only(['email', 'password']);
    return Business::login($credentials);
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array
   */
  public function rules() {
    return [
      'email' => 'required|email',
      'password' => 'required|min:6'
    ];
  }
}
