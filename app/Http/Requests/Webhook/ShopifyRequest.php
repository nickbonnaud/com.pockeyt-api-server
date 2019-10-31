<?php

namespace App\Http\Requests\Webhook;

use Illuminate\Foundation\Http\FormRequest;

class ShopifyRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   *
   * @return bool
   */
  public function authorize() {
    return $this->verifyHeaders();
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array
   */
  public function rules()
  {
    return [
        //
    ];
  }

  private function verifyHeaders() {
    return $this->headers->has('x-shopify-hmac-sha256') && $this->verifyHmac();
  }

  private function verifyHmac() {
    $calcHmac = base64_encode(hash_hmac('sha256', $this->getContent(), env('SHOPIFY_SECRET'), true));
    return hash_equals($calcHmac, $this->header('x-shopify-hmac-sha256'));
  }
}
