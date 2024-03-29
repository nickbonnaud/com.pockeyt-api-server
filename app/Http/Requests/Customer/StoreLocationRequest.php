<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StoreLocationRequest extends FormRequest
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
            'proximity_uuid' => 'required|uuid|exists:beacon_accounts',
            'major' => 'required|integer|exists:beacon_accounts',
            'minor' => 'required|integer|exists:beacon_accounts',
        ];
    }
}
