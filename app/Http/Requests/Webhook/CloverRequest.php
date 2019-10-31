<?php

namespace App\Http\Requests\Webhook;

use Illuminate\Foundation\Http\FormRequest;

class CloverRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   *
   * @return bool
   */
  public function authorize() {
    return  $this->isWebhookVerify() || $this->validateSignature();
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array
   */
  public function rules() {
    if ($this->isWebhookVerify()) {
      return [
        'verificationCode' => 'required|string'
      ];
    } else {
      return [
        "appId" => 'required|string',
        "merchants" => 'required'
      ];
    }
  }

  private function isWebhookVerify() {
    if (env('APP_ENV') == 'testing') {
      return $this->has('verificationCode');
    }
    return $this->has('verificationCode') && is_null(env('CLOVER_SIGNATURE_KEY'));
  }

  private function validateSignature() {
    return $this->header('X-Clover-Auth') == env('CLOVER_SIGNATURE_KEY');
  }
}
