<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\PasswordConfirm;

class UpdateCustomerRequest extends FormRequest
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
           'old_password' => ['required_without:email','required_with:password','min:6', new PasswordConfirm('customer')],
            'password' => 'required_without:email|required_with:old_password|min:6|confirmed',
            'email' => 'required_without_all:old_password,password|email|unique:businesses'
        ];
    }
}
