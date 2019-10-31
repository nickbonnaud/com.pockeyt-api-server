<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerAccountRequest extends FormRequest
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
            'tip_rate' => 'integer|min:0|max:50|required_without:primary',
            'primary' => 'string|in:ach,card|required_without:tip_rate'
        ];
    }
}
