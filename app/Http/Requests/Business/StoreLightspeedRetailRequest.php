<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;

class StoreLightspeedRetailRequest extends FormRequest
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
      'pos_transaction_id' => 'required|integer',
      'customer_identifier' => 'required|uuid|exists:customers,identifier'
    ];
  }
}
