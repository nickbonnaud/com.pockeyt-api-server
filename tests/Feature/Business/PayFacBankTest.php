<?php

namespace Tests\Feature\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PayFacBankTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function test_an_unauthorized_business_cannot_store_bank_data() {
    factory(\App\Models\Business\AccountStatus::class)->create();
    $payFacBank = factory(\App\Models\Business\PayFacBank::class)->make();

    $payFacBankArray = $payFacBank->toArray();
    $payFacBankArray['routing_number'] = $this->createFakeNumber();
    $payFacBankArray['account_number'] = $this->createFakeNumber();

    $response = $this->json('POST', '/api/business/payfac/bank', $payFacBankArray)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_authorized_business_can_store_bank_data() {
    factory(\App\Models\Business\AccountStatus::class)->create();
    $payFacBank = factory(\App\Models\Business\PayFacBank::class)->make();
    $header = $this->businessHeaders($payFacBank->payFacAccount->account->business);

    $payFacBankArray = $payFacBank->toArray();
    $payFacBankArray['routing_number'] = $this->createFakeNumber();
    $payFacBankArray['account_number'] = $this->createFakeNumber();

    $response = $this->json('POST', '/api/business/payfac/bank', $payFacBankArray, $header)->getData();
    $this->assertDatabaseHas('pay_fac_banks', ['first_name' => $payFacBank->first_name, 'last_name' => $payFacBank->last_name]);
    $this->assertEquals($payFacBank->address, $response->data->address->address);
  }

  public function test_an_authorized_business_must_send_correct_data() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $response = $this->json('POST', '/api/business/payfac/bank', [], $this->businessHeaders($business))->assertStatus(422);
    $response = $response->getData();
    $this->assertEquals('The given data was invalid.', $response->message);
  }

  public function test_an_unauthorized_business_cannot_update_bank_data() {
    factory(\App\Models\Business\AccountStatus::class)->create();
    $payFacBank = factory(\App\Models\Business\PayFacBank::class)->create();
    $routing = $payFacBank->routing_number;
    $account = $payFacBank->account_number;
    $newFirstName = 'new name';
    $payFacBankArray = $payFacBank->toArray();

    $payFacBankArray['routing_number'] = $routing;
    $payFacBankArray['account_number'] = $account;
    $payFacBankArray['first_name'] = $newFirstName;

    $response = $this->json('POST', '/api/business/payfac/bank', $payFacBankArray)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_authorized_business_can_update_bank_data() {
    factory(\App\Models\Business\AccountStatus::class)->create();
    $payFacBank = factory(\App\Models\Business\PayFacBank::class)->create();
    $header = $this->businessHeaders($payFacBank->payFacAccount->account->business);
    $routing = $this->createFakeNumber();
    $account = $this->createFakeNumber();
    $newFirstName = 'new name';
    $payFacBankArray = $payFacBank->toArray();

    $payFacBankArray['routing_number'] = $routing;
    $payFacBankArray['account_number'] = $account;
    $payFacBankArray['first_name'] = $newFirstName;

    $response = $this->json('PATCH', "/api/business/payfac/bank/{$payFacBank['identifier']}", $payFacBankArray, $header)->getData();
    $this->assertDatabaseHas('pay_fac_banks', ['first_name' => $newFirstName]);
    $this->assertEquals($routing, $payFacBank->fresh()->routing_number);
    $this->assertEquals($account, $payFacBank->fresh()->account_number);
    $this->assertEquals($newFirstName, $response->data->first_name);
  }

  public function test_an_authorized_business_does_not_change_PII_if_untouched() {
    factory(\App\Models\Business\AccountStatus::class)->create();
    $payFacBank = factory(\App\Models\Business\PayFacBank::class)->create();
    $header = $this->businessHeaders($payFacBank->payFacAccount->account->business);
    $oldRouting = $payFacBank->routing_number;
    $oldAccount = $payFacBank->account_number;
    $routing = 'XXXXX' . substr($payFacBank->routing_number, -4);
    $account = 'XXXXX' . substr($payFacBank->account_number, -4);
    $newFirstName = 'new name';
    $payFacBankArray = $payFacBank->toArray();

    $payFacBankArray['routing_number'] = $routing;
    $payFacBankArray['account_number'] = $account;
    $payFacBankArray['first_name'] = $newFirstName;

    $response = $this->json('PATCH', "/api/business/payfac/bank/{$payFacBank['identifier']}", $payFacBankArray, $header)->getData();
    $this->assertDatabaseHas('pay_fac_banks', ['first_name' => $newFirstName]);
    $this->assertEquals($oldRouting, $payFacBank->fresh()->routing_number);
    $this->assertEquals($oldAccount, $payFacBank->fresh()->account_number);
    $this->assertEquals($newFirstName, $response->data->first_name);
  }



  private function createFakeNumber() {
    return $this->faker->randomNumber($nbDigits = 9, $strict = true);
  }
}
