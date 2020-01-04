<?php

namespace App\Http\Controllers\Customer;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use App\Http\Requests\Business\ResetPasswordRequest;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use App\Http\Requests\Customer\RequestPasswordResetRequest;

class AuthResetPasswordController extends Controller {

  use SendsPasswordResetEmails;

  public function __construct() {
    $this->middleware('guest');
  }

  public function requestReset(RequestPasswordResetRequest $request) {
    $response = $this->broker()->sendResetLink($request->only('email'));

  	return $response == Password::RESET_LINK_SENT
  		? $this->sendResetLinkResponse($request, $response)
  		: $this->sendResetLinkFailedResponse($request, $response);
  }

  public function reset(ResetPasswordRequest $request) {
    $response = $this->broker()->reset(
      $this->getResetCredentials($request), function($customer, $password) {
        $this->resetPassword($customer, $password);
      }
    );

    return $response = Password::PASSWORD_RESET
      ? $this->sendResetResponse($request, $response)
      : $this->sendResetFailedResponse($request, $response);
  }








  protected function getResetCredentials(Request $request) {
    $credentials = $request->only('password', 'password_confirmation', 'token');
    $credentials['email'] =  DB::table('password_resets')->where('token', $request->token)->first()->email;
    return $credentials;
  }

  protected function resetPassword($customer, $password) {
    $customer->password = $password;
    $customer->setRememberToken(Str::random(60));
    $customer->save();
    event(new PasswordReset($customer));
  }

  protected function sendResetLinkResponse(Request $request, $response) {
  	return response()->json([
  		'data' => [
  			'email_sent' => true,
  			'res' => $response
  		]
  	], 200);
  }

  protected function sendResetLinkFailedResponse(Request $request, $response) {
  	return response()->json([
  		'data' => [
  			'email_sent' => false,
  			'res' => $response
  		]
  	], 500);
  }

  protected function sendResetResponse(Request $request, $response) {
    return response()->json([
      'data' => [
        'reset' => true,
        'res' => $response
      ]
    ], 200);
  }

  protected function sendResetFailedResponse(Request $request, $response) {
    return response()->json([
      'data' => [
        'reset' => false,
        'res' => $response
      ]
    ], 500);
  }




  public function broker() {
    return Password::broker('customers');
  }
}
