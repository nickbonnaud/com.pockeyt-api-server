<?php

namespace Tests\Feature\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Handlers\Http\HttpHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CloverAccountHttpTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_changing_customer_id_updates_order_on_clover() {
    $merchantId = 'RR9ACXMZ6AFA1';
    $orderId = '5J8SRR9Z5PKB8';

    factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'paid_clover']);
    $status = factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'paid']);
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'clover']);
    $cloverAccount = factory(\App\Models\Business\CloverAccount::class)->create(['merchant_id' => $merchantId, 'pos_account_id' => $posAccount->id, 'access_token' => env('CLOVER_SANDBOX_TOKEN')]);
    $customerProfile = factory(\App\Models\Customer\CustomerProfile::class)->create();
    $transaction = factory(\App\Models\Transaction\Transaction::class)->create([
      'customer_id' => $customerProfile->customer->id,
      'business_id' => $cloverAccount->posAccount->business_id,
      'pos_transaction_id' => $orderId,
    ]);

    sleep(3);
    $response = $this->fetchOrder($cloverAccount, $merchantId, $orderId);
    $this->assertEquals($response['note'], env('BUSINESS_NAME') . " customer: {$customerProfile->first_name} {$customerProfile->last_name}");

    $newCustomerProfile = factory(\App\Models\Customer\CustomerProfile::class)->create();
    $transaction->update([
      'customer_id' => $newCustomerProfile->customer->id
    ]);
    sleep(3);
    $response = $this->fetchOrder($cloverAccount, $merchantId, $orderId);
    $this->assertEquals($response['note'], env('BUSINESS_NAME') . " customer: {$newCustomerProfile->first_name} {$newCustomerProfile->last_name}");

    $newNewCustomerProfile = factory(\App\Models\Customer\CustomerProfile::class)->create();
    $transaction->update([
      'customer_id' => $newNewCustomerProfile->customer->id
    ]);
    sleep(3);
    $response = $this->fetchOrder($cloverAccount, $merchantId, $orderId);
    $this->assertEquals($response['note'], env('BUSINESS_NAME') . " customer: {$newNewCustomerProfile->first_name} {$newNewCustomerProfile->last_name}");
  }


  private function fetchOrder($cloverAccount, $merchantId, $orderId) {
    $url = $cloverAccount->setUrlValue('merchant_id', $merchantId, config('urls.clover.order'));
    $url = $cloverAccount->getBaseUrl() . $cloverAccount->setUrlValue('order_id', $orderId, $url);
    $headers = $cloverAccount->getHeaders();

    $response = $cloverAccount->createHttpHandler()->get($url, $headers);
    return $cloverAccount->parseHttpResponse($response);
  }
}
