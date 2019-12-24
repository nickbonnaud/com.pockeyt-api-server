<?php

namespace App\Http\Requests\Business;

use Illuminate\Foundation\Http\FormRequest;

class StoreReplyRequest extends FormRequest {
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
      'message_identifier' => 'required|uuid|exists:business_messages,identifier',
      'body' => 'required|string|min:2',
      'sent_by_business' => 'required|boolean'
    ];
  }
}
