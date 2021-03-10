<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\PasswordConfirm;

class UpdateBusinessRequest extends FormRequest
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
            'password' => 'required_without:email|min:6|confirmed',
            'email' => 'required_without_all:password|email|unique:businesses'
        ];
    }
}
