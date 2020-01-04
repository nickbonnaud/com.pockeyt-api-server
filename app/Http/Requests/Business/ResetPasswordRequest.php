<?php

namespace App\Http\Requests\Business;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
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
            'password' => 'required|confirmed|min:6',
            'token' => ['required', 'exists:password_resets', function($attribute, $value, $fail) {
                $resetToken = DB::table('password_resets')->where('token', $value)->first();
                if ($resetToken) {
                    if (Carbon::now()->diffInMinutes($resetToken->created_at) > 60) {
                       return $fail("Reset token has expired.");
                    }
                }
            }]
        ];
    }
}
