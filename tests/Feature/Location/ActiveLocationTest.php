<?php

namespace Tests\Feature\Location;

use Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActiveLocationTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function test_deleting_an_active_location_creates_a_historic_location() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $geoAccount = factory(\App\Models\Business\GeoAccount::class)->create();
    $this->createAccounts($geoAccount);
    $activeLocation = factory(\App\Models\Location\ActiveLocation::class)->create(['customer_id' => $customer->id, 'location_id' => $geoAccount->location_id]);

    $this->assertDatabaseHas('active_locations', ['id' => $activeLocation->id]);
    $this->assertDatabaseMissing('historic_locations', ['customer_id' => $activeLocation->customer_id, 'location_id' => $activeLocation->location_id]);
    $activeLocation->delete();
    $this->assertDatabaseMissing('active_locations', ['id' => $activeLocation->id, 'customer_id' => $activeLocation->customer_id]);
    $this->assertDatabaseHas('historic_locations', ['customer_id' => $activeLocation->customer_id, 'location_id' => $activeLocation->location_id]);
  }

  public function test_an_unauth_customer_cannot_create_an_active_location() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $geoAccount = factory(\App\Models\Business\GeoAccount::class)->create();
    $this->createAccounts($geoAccount);

    $attributes = [
      'action' => 'enter',
      'lat' => $geoAccount->lat,
      'lng' => $geoAccount->lng
    ];

    $response = $this->json('POST', "/api/customer/location/{$geoAccount->identifier}", $attributes)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_customer_must_post_correct_data() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $geoAccount = factory(\App\Models\Business\GeoAccount::class)->create();
    $this->createAccounts($geoAccount);
    $headers = $this->customerHeaders($customer);

    $attributes = [
      'action' => 'not_correct',
      'lat' => 'bcjiab8383u83',
      'lng' => '1000'
    ];

    $response = $this->json('POST', "/api/customer/location/{$geoAccount->identifier}", $attributes)->assertStatus(422);
    $response = $response->getData();

    $this->assertEquals('The selected action is invalid.', $response->errors->action[1]);
    $this->assertEquals('The lat must be a number.', $response->errors->lat[0]);
    $this->assertEquals('The lng may not be greater than 180.', $response->errors->lng[0]);
  }

  public function test_an_auth_customer_can_create_an_active_location() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $geoAccount = factory(\App\Models\Business\GeoAccount::class)->create();
    $this->createAccounts($geoAccount);
    $headers = $this->customerHeaders($customer);

    $attributes = [
      'action' => 'enter',
      'lat' => $geoAccount->lat,
      'lng' => $geoAccount->lng
    ];

    $response = $this->json('POST', "/api/customer/location/{$geoAccount->identifier}", $attributes, $headers)->getData();
    $this->assertDatabaseHas('active_locations', ['identifier' => $response->data->active_location_id, 'customer_id' => $customer->id]);
  }

  public function test_an_unauth_customer_cannot_update_an_active_location() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $geoAccount = factory(\App\Models\Business\GeoAccount::class)->create();
    $this->createAccounts($geoAccount);
    $activeLocation = factory(\App\Models\Location\ActiveLocation::class)->create(['customer_id' => $customer->id, 'location_id' => $geoAccount->location_id]);

    $attributes = [
      'action' => 'static',
      'lat' => $geoAccount->lat,
      'lng' => $geoAccount->lng
    ];

    $response = $this->json('PATCH', "/api/customer/location/{$activeLocation->identifier}", $attributes)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_customer_can_update_their_active_location() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $geoAccount = factory(\App\Models\Business\GeoAccount::class)->create();
    $this->createAccounts($geoAccount);
    $oldTimeStamp = (Carbon::now())->subHour();
    $activeLocation = factory(\App\Models\Location\ActiveLocation::class)->create(['customer_id' => $customer->id, 'location_id' => $geoAccount->location_id, 'updated_at' => $oldTimeStamp]);

    $headers = $this->customerHeaders($customer);

    $attributes = [
      'action' => 'static',
      'lat' => $geoAccount->lat,
      'lng' => $geoAccount->lng
    ];

    $response = $this->json('PATCH', "/api/customer/location/{$activeLocation->identifier}", $attributes, $headers)->getData();
    $this->assertNotEquals($oldTimeStamp, $activeLocation->fresh()->updated_at);
  }

  public function test_an_unauth_customer_cannot_delete_an_active_location() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $geoAccount = factory(\App\Models\Business\GeoAccount::class)->create();
    $this->createAccounts($geoAccount);
    $activeLocation = factory(\App\Models\Location\ActiveLocation::class)->create(['location_id' => $geoAccount->location->id, 'customer_id' =>$customer->id]);

    $attributes = [
      'action' => 'exit',
      'lat' => $geoAccount->lat,
      'lng' => $geoAccount->lng
    ];

    $response = $this->json('DELETE', "/api/customer/location/{$activeLocation->identifier}", $attributes)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_customer_can_delete_an_active_location_no_transaction() {
    $geoAccount = factory(\App\Models\Business\GeoAccount::class)->create();
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['business_id' => $geoAccount->location->business_id, 'type' => 'shopify']);
    $activeLocation = factory(\App\Models\Location\ActiveLocation::class)->create(['location_id' => $geoAccount->location->id, 'transaction_id' => null]);
    $customer = $activeLocation->customer;

    
    $headers = $this->customerHeaders($customer);

    $attributes = [
      'action' => 'exit',
      'lat' => $geoAccount->lat,
      'lng' => $geoAccount->lng
    ];

    $response = $this->json('DELETE', "/api/customer/location/{$activeLocation->identifier}", $attributes, $headers)->getData();
    $this->assertEquals("Location removed.", $response->data->success);
    $this->assertDatabaseMissing('active_locations', ['id' => $activeLocation->id]);
  }

  public function test_an_auth_customer_cannot_delete_active_location_with_transaction() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $geoAccount = factory(\App\Models\Business\GeoAccount::class)->create();
    $this->createAccounts($geoAccount);
    $transaction = factory(\App\Models\Transaction\Transaction::class)->create(['customer_id' =>$customer->id, 'business_id' => $geoAccount->location->business->id]);
    $activeLocation = factory(\App\Models\Location\ActiveLocation::class)->create(['location_id' => $geoAccount->location->id, 'customer_id' =>$customer->id, 'transaction_id' => $transaction->id]);
    $headers = $this->customerHeaders($customer);

    $attributes = [
      'action' => 'exit',
      'lat' => $geoAccount->lat,
      'lng' => $geoAccount->lng
    ];

    $response = $this->json('DELETE', "/api/customer/location/{$activeLocation->identifier}", $attributes, $headers)->getData();
    $this->assertEquals($activeLocation->identifier, $response->data->active_location_id);
    $this->assertEquals($transaction->identifier, $response->data->transaction_id);
    $this->assertDatabaseHas('active_locations', ['id' => $activeLocation->id]);
  }

  public function test_a_creating_an_active_location_creates_bill_identifier_square() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $geoAccount = factory(\App\Models\Business\GeoAccount::class)->create();
    $this->createAccounts($geoAccount);
    $headers = $this->customerHeaders($customer);

    $attributes = [
      'action' => 'enter',
      'lat' => $geoAccount->lat,
      'lng' => $geoAccount->lng
    ];

    $response = $this->json('POST', "/api/customer/location/{$geoAccount->identifier}", $attributes, $headers)->getData();
    $this->assertDatabaseHas('active_locations', ['bill_identifier' => 'JDKYHBWT1D4F8MFH63DBMEN8Y4']);
  }





  private function createAccounts($geoAccount) {
    factory(\App\Models\Business\AccountStatus::class)->create();
    $posStatus = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $posStatus->id, 'business_id' => $geoAccount->location->business_id]);
    $account = factory(\App\Models\Business\Account::class)->create(['business_id' => $posAccount->business->id]);
    $payFacAccount = factory(\App\Models\Business\PayFacAccount::class)->create(['account_id' => $account->id]);
    $payFacBusinessAccount = factory(\App\Models\Business\PayFacBusiness::class)->create(['pay_fac_account_id' => $payFacAccount->id]);
    factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'closed']);
    factory(\App\Models\Business\SquareAccount::class)->create(['pos_account_id' => $posAccount->id]);
  }
}
