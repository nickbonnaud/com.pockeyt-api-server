<?php

namespace App\Http\Requests\Business;

use Carbon\Carbon;
use App\Models\Customer\ResetCode;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest {
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
      'password' => 'required|confirmed|min:8',
      'email' => 'required|exists:customers',
      'reset_code' => ['required', 'exists:reset_codes,value', function($attribute, $value, $fail) {
        $resetCode = ResetCode::where('value', $value)->first();
        if ($resetCode == null) return $fail("Invalid Reset Code");
        if ($resetCode->customer->email != $this->email) $fail("Invalid Reset Code");
        if (Carbon::now()->diffInMinutes($resetCode->created_at) > 10) return $fail("Reset token has expired.");
      }]
    ];
  }
}
