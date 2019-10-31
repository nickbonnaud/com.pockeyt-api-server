<?php

namespace Tests\Feature\Webhook;

use Tests\TestCase;
use Illuminate\Support\Str;
use App\Helpers\TestHelpers;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SquareTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_a_square_post_request_must_have_valid_signature() {
    $customer = factory(\App\Models\Customer\Customer::class)->create(['identifier' => 'b8aa8380-6799-11e9-b2da-0fc14cd3f31e']);
    $this->createRequiredAccounts($customer);
    $baseUrl = "http://localhost";
    $url = "/api/webhook/square";

    $attributes = [
      "merchant_id" => "18YC4JBH91E1H",
      "location_id" => "JGHJ0343",
      "event_type" => "PAYMENT_UPDATED",
      "entity_id" => "Jq74mCczmFXk1tC10GB"
    ];

    $response = $this->json('POST', $url, $attributes)->assertStatus(403);
  }

  public function test_a_square_post_request_must_have_correct_data() {
    $customer = factory(\App\Models\Customer\Customer::class)->create(['identifier' => 'b8aa8380-6799-11e9-b2da-0fc14cd3f31e']);
    factory(\App\Models\Customer\PushToken::class)->create(['customer_id' => $customer->id]);
    $this->createRequiredAccounts($customer);
    $baseUrl = "http://localhost";
    $url = "/api/webhook/square";

    $attributes = [
      "merchant_id" => "18YC4JBH91E1H",
      "location_id" => "JGHJ0343",
      "event_type" => "PAYMENT_UPDATED"
    ];

    $headers = $this->squareWebhookHeaders($baseUrl . $url, $attributes);
    $response = $this->json('POST', $url, $attributes, $headers)->assertStatus(422);
    $this->assertEquals("The entity id field is required.", $response->getData()->errors->entity_id[0]);
  }

  public function test_a_square_webhook_request_creates_transaction() {
    $customer = factory(\App\Models\Customer\Customer::class)->create(['identifier' => 'b8aa8380-6799-11e9-b2da-0fc14cd3f31e']);
    factory(\App\Models\Customer\PushToken::class)->create(['customer_id' => $customer->id]);
    $posAccount = $this->createRequiredAccounts($customer);

    $merchantId = "3MYCJG5GVYQ8Q";
    $locationId = "18YC4JDH91E1H";

    $squareAccount = factory(\App\Models\Business\SquareAccount::class)->create(['pos_account_id' => $posAccount->id, 'merchant_id' => $merchantId, 'location_id' => $locationId]);

    $baseUrl = "http://localhost";
    $url = "/api/webhook/square";

    $attributes = [
      "merchant_id" => $merchantId,
      "location_id" => $locationId,
      "event_type" => "PAYMENT_UPDATED",
      "entity_id" => "Jq74mCczmFXk1tC10GB"
    ];

    $headers = $this->squareWebhookHeaders($baseUrl . $url, $attributes);
    $response = $this->json('POST', $url, $attributes, $headers)->getData();
    $this->assertEquals("Received.", $response->success);
    $this->assertDatabaseHas('transactions', ['customer_id' => $customer->id, 'business_id' => $posAccount->business->id]);
    $this->assertDatabaseHas('purchased_items', ['transaction_id' => $customer->transactions->first()->id]);
    $this->assertEquals(count(json_decode(TestHelpers::fakeSquarePaymentFetch())->itemizations), count(($customer->transactions->first())->purchasedItems));
  }

  public function test_a_square_webhook_transaction_with_employee_creates_employee_if_employee_not_stored() {
    $customer = factory(\App\Models\Customer\Customer::class)->create(['identifier' => 'b8aa8380-6799-11e9-b2da-0fc14cd3f31e']);
    factory(\App\Models\Customer\PushToken::class)->create(['customer_id' => $customer->id]);
    $posAccount = $this->createRequiredAccounts($customer);

    $merchantId = "3MYCJG5GVYQ8Q";
    $locationId = "18YC4JDH91E1H";

    $squareAccount = factory(\App\Models\Business\SquareAccount::class)->create(['pos_account_id' => $posAccount->id, 'merchant_id' => $merchantId, 'location_id' => $locationId]);

    $baseUrl = "http://localhost";
    $url = "/api/webhook/square";

    $attributes = [
      "merchant_id" => $merchantId,
      "location_id" => $locationId,
      "event_type" => "PAYMENT_UPDATED",
      "entity_id" => "Jq74mCczmFXk1tC10GB"
    ];

    $headers = $this->squareWebhookHeaders($baseUrl . $url, $attributes);
    $externalEmployeeId = json_decode(TestHelpers::fakeSquarePaymentFetch())->tender[0]->employee_id;

    $this->assertDatabaseMissing('employees', ['external_id' => $externalEmployeeId]);
    $response = $this->json('POST', $url, $attributes, $headers)->getData();
    $transaction = \App\Models\Transaction\Transaction::first();
    $status = \App\Models\Transaction\TransactionStatus::where(['name' => 'paid'])->first();
    $transaction->update(['status_id' => $status->id]);
    sleep(1);
    $employee = \App\Models\Business\Employee::first();
    $this->assertEquals($employee->external_id, $externalEmployeeId);
    $this->assertDatabaseHas('employees', ['external_id' => $externalEmployeeId]);
  }

  public function test_a_square_webhook_transaction_with_employee_does_not_create_employee_if_already_stored() {
    $customer = factory(\App\Models\Customer\Customer::class)->create(['identifier' => 'b8aa8380-6799-11e9-b2da-0fc14cd3f31e']);
    factory(\App\Models\Customer\PushToken::class)->create(['customer_id' => $customer->id]);
    $posAccount = $this->createRequiredAccounts($customer);

    $merchantId = "3MYCJG5GVYQ8Q";
    $locationId = "18YC4JDH91E1H";

    $squareAccount = factory(\App\Models\Business\SquareAccount::class)->create(['pos_account_id' => $posAccount->id, 'merchant_id' => $merchantId, 'location_id' => $locationId]);

    $baseUrl = "http://localhost";
    $url = "/api/webhook/square";

    $attributes = [
      "merchant_id" => $merchantId,
      "location_id" => $locationId,
      "event_type" => "PAYMENT_UPDATED",
      "entity_id" => "Jq74mCczmFXk1tC10GB"
    ];

    $headers = $this->squareWebhookHeaders($baseUrl . $url, $attributes);
    $externalEmployeeId = json_decode(TestHelpers::fakeSquarePaymentFetch())->tender[0]->employee_id;
    factory(\App\Models\Business\Employee::class)->create(['external_id' => $externalEmployeeId, 'business_id' => $posAccount->business_id]);

    $this->assertEquals(1, \App\Models\Business\Employee::count());
    $response = $this->json('POST', $url, $attributes, $headers)->getData();
    $transaction = \App\Models\Transaction\Transaction::first();
    $status = \App\Models\Transaction\TransactionStatus::where(['name' => 'paid'])->first();
    $transaction->update(['status_id' => $status->id]);
    $this->assertEquals(1, \App\Models\Business\Employee::count());
  }

  public function test_a_square_webhook_request_creates_sends_notification() {
    $customer = factory(\App\Models\Customer\Customer::class)->create(['identifier' => 'b8aa8380-6799-11e9-b2da-0fc14cd3f31e']);
    factory(\App\Models\Customer\PushToken::class)->create(['customer_id' => $customer->id]);
    $posAccount = $this->createRequiredAccounts($customer);

    $merchantId = "3MYCJG5GVYQ8Q";
    $locationId = "18YC4JDH91E1H";

    $squareAccount = factory(\App\Models\Business\SquareAccount::class)->create(['pos_account_id' => $posAccount->id, 'merchant_id' => $merchantId, 'location_id' => $locationId]);

    $baseUrl = "http://localhost";
    $url = "/api/webhook/square";

    $attributes = [
      "merchant_id" => $merchantId,
      "location_id" => $locationId,
      "event_type" => "PAYMENT_UPDATED",
      "entity_id" => "Jq74mCczmFXk1tC10GB"
    ];

    $headers = $this->squareWebhookHeaders($baseUrl . $url, $attributes);
    $response = $this->json('POST', $url, $attributes, $headers)->getData();
    $transaction = $customer->transactions->first();
    $this->assertDatabaseHas('transaction_notifications', ['transaction_id' => $transaction->id, 'bill_closed_sent' => true]);
  }




  private function createRequiredAccounts($customer) {
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    factory(\App\Models\Business\AccountStatus::class)->create();
    $posStatus = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $posStatus->id]);
    $account = factory(\App\Models\Business\Account::class)->create(['business_id' => $posAccount->business->id]);
    $payFacAccount = factory(\App\Models\Business\PayFacAccount::class)->create(['account_id' => $account->id]);
    $payFacBusinessAccount = factory(\App\Models\Business\PayFacBusiness::class)->create(['pay_fac_account_id' => $payFacAccount->id]);
    $squareAccount = factory(\App\Models\Business\SquareAccount::class)->create(['pos_account_id' => $posAccount->id]);
    $location = factory(\App\Models\Business\Location::class)->create(['business_id' => $posAccount->business->id]);
    factory(\App\Models\Location\ActiveLocation::class)->create(['location_id' => $location->id, 'customer_id' => $customer->id]);
    factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'closed']);
    $profile = factory(\App\Models\Business\Profile::class)->create(['business_id' => $posAccount->business->id]);
    $profile->photos->logo_id = factory(\App\Models\Business\Photo::class)->create()->id;
    $profile->photos->save();
    return $posAccount;
  }
}
