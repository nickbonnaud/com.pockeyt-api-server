<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\Business\Business;
use App\Models\Customer\Customer;
use Illuminate\Support\Str;
use App\Models\Admin\Admin;

abstract class TestCase extends BaseTestCase {
  use CreatesApplication;

  protected function businessHeaders($business) {
  	$headers = ['Accept' => 'application/json'];
  	$token = (Business::createToken($business))['token'];
  	$headers['Authorization'] = 'Bearer '.$token;

  	return $headers;
  }

  protected function createBusinessToken($business) {
    return auth('business')->claims(['csrf-token' => Str::random(32)])->login($business);
  }

  protected function send($token, $method, $url, $body = []) {
    $headers = ['Accept' => 'application/json'];

    if (!is_null(auth('business')->user())) {
      $headers['csrf-token'] = auth('business')->payload()->get('csrf-token');
    }

    return $this
      ->withCookie('jwt', $token)
      ->withHeaders($headers)
      ->$method($url, $body);
  }

  protected function customerHeaders($customer) {
  	$headers = ['Accept' => 'application/json'];
  	$token = (Customer::createToken($customer))['token'];
  	$headers['Authorization'] = 'Bearer '.$token;

  	return $headers;
  }

  protected function adminHeaders($admin) {
    $headers = ['Accept' => 'application/json'];
    $token = $admin->createToken();
    $headers['Authorization'] = 'Bearer '.$token;

    return $headers;
  }

  protected function squareWebhookHeaders($url, $body) {
    $headers = ['Accept' => 'application/json'];
    $headers['X-Square-Signature'] = $this->createSquareSignature($url, $body);
    return $headers;
  }

  protected function cloverWebhookHeader() {
    $headers = ['Accept' => 'application/json'];
    $headers['X-Clover-Auth'] = env('CLOVER_SIGNATURE_KEY');
    return $headers;
  }


  private function createSquareSignature($url, $body) {
    $stringToSign = str_replace(" ", "", ($url . json_encode($body)));
    return base64_encode(hash_hmac('sha1', $stringToSign, env('TEST_SQUARE_SIGNATURE_KEY'), true));
  }
}
