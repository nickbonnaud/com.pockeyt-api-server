<?php

namespace App\Http\Requests\Webhook;

use Illuminate\Foundation\Http\FormRequest;

class SquareRequest extends FormRequest {
  /**
   * Determine if the user is authorized to make this request.
   *
   * @return bool
   */
  public function authorize() {
    return $this->headers->has('X-Square-Signature') && $this->validateSignature();
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array
   */
  public function rules() {
    return [
      "merchant_id" => 'required|string',
      "location_id" => 'required|string',
      "event_type" => 'required|string|in:PAYMENT_UPDATED',
      "entity_id" => 'required|string'
    ];
  }

  


  private function validateSignature() {
    $stringToSign = str_replace(" ", "", ($this->url() . $this->getContent()));
    $signature = base64_encode(hash_hmac('sha1', $stringToSign, env('TEST_SQUARE_SIGNATURE_KEY'), true));
    return $this->header('X-Square-Signature') == $signature;
  }
}
