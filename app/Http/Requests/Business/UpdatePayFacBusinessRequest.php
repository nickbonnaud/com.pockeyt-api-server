<?php

namespace App\Http\Requests\Business;

use App\Rules\ValidRegion;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePayFacBusinessRequest extends FormRequest {
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
  public function rules(){
    return [
      'ein' => ['required_unless:entity_type,soleProprietorship', 'regex:/^\d{2}\-?\d{7}$/'],
      'business_name' => 'required|string|min:2',
      'state' => 'required|string|alpha|size:2',
      'zip' => 'required|numeric|digits:5',
      'neighborhood' => 'string',
      'city' => ['required', 'string', new ValidRegion($this->only(['state', 'zip', 'neighborhood']))],
      'address' => 'required|string',
      'address_secondary' => 'string|nullable',
      'entity_type' => 'required|string|in:soleProprietorship,corporation,llc,partnership'
    ];
  }
}
