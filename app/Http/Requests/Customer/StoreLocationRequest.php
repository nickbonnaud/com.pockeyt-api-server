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
            'action' => 'required|string|alpha|in:enter,exit,static',
            'lat' => 'required|numeric|min:-90|max:90',
            'lng' => 'required|numeric|min:-180|max:180'
        ];
    }
}
