<?php

namespace Tests\Feature\Business;

use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
use App\Handlers\Http\HttpHandler;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SquareAccountHttpTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function test_a_square_account_fetches_correct_locationId() {
    $squareAccount = factory(\App\Models\Business\SquareAccount::class)->create(['access_token' => env('SQUARE_PRIVATE_ACCESS')]);
    $this->assertEquals('AJF0JT16KR0AS', $squareAccount->location_id);
    
  }

  public function test_a_square_account_stores_all_inventory() {
    $squareAccount = factory(\App\Models\Business\SquareAccount::class)->create(['access_token' => env('SQUARE_PRIVATE_ACCESS')]);
    $items = $squareAccount->posAccount->business->inventory->activeItems;
    $this->assertNotNull($items);
  }

  public function test_creating_an_active_location_creates_a_customer_in_square() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $profile =  factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $location = factory(\App\Models\Business\Location::class)->create();
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['business_id' => $location->business_id]);
    $squareAccount = factory(\App\Models\Business\SquareAccount::class)->create(['access_token' => env('SQUARE_PRIVATE_ACCESS'), 'pos_account_id' => $posAccount->id]);

    $activeLocation = factory(\App\Models\Location\ActiveLocation::class)->create(['customer_id' => $customer->id, 'location_id' => $location->id]);
    $this->assertNotNull($activeLocation->bill_identifier);

    $url = $this->getBaseUrl() . "v2/customers/{$activeLocation->bill_identifier}";
    $headers = $this->getHeaders(env('SQUARE_PRIVATE_ACCESS'));
    $response = ($this->createHttpHandler()->get($url, $headers))->json();
    $this->assertEquals(strtolower(env('BUSINESS_NAME')) . "_" . $customer->identifier, $response['customer']['reference_id']);
  }

  public function test_a_square_account_can_create_a_transaction_after_receiving_webhook() {
    Notification::fake();
    factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'closed']);
    $customer = factory(\App\Models\Customer\Customer::class)->create(['identifier' => 'b8aa8380-6799-11e9-b2da-0fc14cd3f31e']);
    $profile = factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $location = factory(\App\Models\Business\Location::class)->create();
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['business_id' => $location->business_id]);
    $squareAccount = factory(\App\Models\Business\SquareAccount::class)->create(['access_token' => env('SQUARE_PRIVATE_ACCESS'), 'pos_account_id' => $posAccount->id]);
    $activeLocation = factory(\App\Models\Location\ActiveLocation::class)->create(['customer_id' => $customer->id, 'location_id' => $location->id]);

    $webhookData = [
      'entity_id' => 'FA1HYbBCfyDXLc4dLIVCLQB'
    ];

    $squareAccount->fetchPayment($webhookData);
    $this->assertDatabaseHas('transactions', ['customer_id' => $customer->id, 'business_id' => $location->business_id]);
  }

  // public function test_a_square_account_can_delete_customers() {
  //   $response = $this->fetchCustomers();
  //   $count = count($response['customers']);
  //   if ($count == 0) {
  //     $customerId = $this->createCustomer();
  //   } else {
  //     $customerId = $response['customers'][0]['id'];
  //   }

  //   $location = factory(\App\Models\Business\Location::class)->create();
  //   $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['business_id' => $location->business_id]);
  //   $squareAccount = factory(\App\Models\Business\SquareAccount::class)->create(['access_token' => env('SQUARE_PRIVATE_ACCESS'), 'pos_account_id' => $posAccount->id]);
  //   $squareAccount->destroyCustomer($customerId);

  //   $response = $this->fetchCustomers();
  //   $this->assertNotEquals($count, count($response['customers']));
  //   if (count($response['customers']) > 0) {
  //     $this->assertNotEquals($customerId, $response['customers'][0]);
  //   }
  // }

  private function fetchCustomers() {
    $url = $this->getBaseUrl() . 'v2/customers';
    $headers = $this->getHeaders(env('SQUARE_PRIVATE_ACCESS'));
    $response = $this->createHttpHandler()->get($url, $headers);
    return $response->json();
  }

  private function createCustomer() {
    $url = $this->getBaseUrl() . config('urls.square.create_customer');
    $headers = $this->getHeaders(env('SQUARE_PRIVATE_ACCESS'));
    $body = [
      'idempotency_key' => Str::random(25),
      'given_name' => $customer->profile->first_name,
      'family_name' => $customer->profile->last_name,
      'email_address' => $customer->email,
      'reference_id' => strtolower(env('BUSINESS_NAME')) . "_" . $customer->identifier,
      'note' => env('BUSINESS_NAME') . " Customer"
    ];
    $response = $this->createHttpHandler()->post($url, $headers, $body);
    return ($response->json())['customer']['id'];
  }

  public function test_a_square_account_can_create_a_refund_after_webhook() {
    factory(\App\Models\Refund\RefundStatus::class)->create(['name' => 'refund_pending']);
    $status = factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'paid']);
    $location = factory(\App\Models\Business\Location::class)->create();
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['business_id' => $location->business_id]);
    $squareAccount = factory(\App\Models\Business\SquareAccount::class)->create(['access_token' => env('SQUARE_PRIVATE_ACCESS'), 'pos_account_id' => $posAccount->id]);
    $customer = factory(\App\Models\Customer\Customer::class)->create(['identifier' => 'b8aa8380-6799-11e9-b2da-0fc14cd3f31e']);
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $transaction = factory(\App\Models\Transaction\Transaction::class)->create([
      'customer_id' => $customer->id,
      'business_id' => $squareAccount->posAccount->business_id,
      'status_id' => $status->id,
      'pos_transaction_id' => 'dsF9dAdyuMG1Zfia3II9mV0eV',
    ]);

    $webhookData = [
      'entity_id' => 'lu6mmh5LDsAqzD2qTCG6JQB'
    ];

    $squareAccount->fetchPayment($webhookData);
    $this->assertDatabaseHas('refunds', ['transaction_id' => $transaction->id, 'pos_refund_id' => 'S2JRKxv6u1o0B9Hf3f95S']);
  }

  // public function test_get_transactions_after_refund() {
  //   $url = $this->getBaseUrl() . "v1/AJF0JT16KR0AS/payments/lu6mmh5LDsAqzD2qTCG6JQB";
  //   $headers = $this->getHeaders(env('SQUARE_PRIVATE_ACCESS'));
  //   $response = $this->createHttpHandler()->get($url, $headers);
  //   $response = $response->json();

  //   $employeeId = Arr::get($response, 'tender.0.employee_id', null);
  //   dd($employeeId);
  // }

  // public function test_fetch_square_location_id() {
  //   $url = $this->getBaseUrl() . config('urls.square.locations');
  //   $headers = $this->getHeaders();
  //   $response = $this->createHttpHandler()->get($url, $headers);
  //   $locationId = $this->parseLocationResponseForId($response->json());
  //   $this->assertEquals("CBASEBWU7ej2CWpEQhRnKrTjCowgAQ", $locationId);
  // }

  // public function parseLocationResponseForId($response) {
  //   if (count($response['locations']) == 1) {
  //     return ($response['locations'][0])->id;
  //   } else {
  //     foreach ($response['locations'] as $location) {
  //       if (strtolower($location['address']['address_line_1']) == strtolower($this->getBusinessAddress())) {
  //         return $location['id'];
  //       }
  //     }
  //   }
  // }

  // public function test_fetch_square_inventory_items() {
  //   $this->fetchInventoryItems();
  // }

  // private function fetchInventoryItems($url = null) {
  //   $locationId = "AJF0JT16KR0AS";
  //   $url = $url ? $url : $this->getBaseUrl() . $this->setUrlValue('location_id', $locationId, config('urls.square.inventory'));
  //   $headers = $this->getHeaders(env('SQUARE_PRIVATE_ACCESS'));
  //   $response = $this->createHttpHandler()->get($url, $headers);
  //   dd($response->json());
  //   $this->storeInventoryItems($response->json());
  //   if ($nextUrl = $this->getPaginateUrl($response)) {
  //     $this->fetchInventoryItems($nextUrl);
  //   }
  // }

  // public function test_fetch_square_transaction() {
  //   $locationId = "AJF0JT16KR0AS";
  //   $url = $this->getBaseUrl() . "v2/locations/{$locationId}/transactions";
  //   $headers = $this->getHeaders(env('SQUARE_PRIVATE_ACCESS'));
  //   $response = $this->createHttpHandler()->get($url, $headers);
  //   dd($response->json());

  //   $transactionId = str_replace("/", "", (strrchr($paymentResponse->payment_url, '/')));
  //   $cleanUrl = $this->setUrlValue('location_id', $this->location_id, config('urls.square.transaction'));
  //   $cleanUrl = $this->setUrlValue('transaction_id', $transactionId, $cleanUrl);
  //   $url = $this->getBaseUrl() . $cleanUrl;
  //   $headers = $this->getHeaders();
  // }

  // public function getBusinessAddress() {
  //   return "375 West Broadway";
  // }




  private function getBaseUrl() {
    return config('urls.square.base');
  }

  private function getHeaders($token = null) {
    $token = $token ? $token : env('SQUARE_SANDBOX_ACCESS_TOKEN');
    return [
      "Authorization" => "Bearer " . $token, 
      "Content-Type" => "application/json"
    ];
  }

  // private function setUrlValue($key, $value, $url) {
  //   return str_replace("<{$key}>", $value, $url);
  // }

  private function createHttpHandler() {
    return new HttpHandler();
  }
}
