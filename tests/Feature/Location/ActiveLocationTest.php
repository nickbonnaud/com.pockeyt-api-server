<?php

namespace Tests\Feature\Location;

use Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Notifications\Customer\EnterBusiness;
use App\Notifications\Customer\ExitBusiness;
use App\Notifications\Customer\FixBill;
use Illuminate\Support\Facades\Notification;
use App\Models\Transaction\TransactionNotification;

class ActiveLocationTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_deleting_an_active_location_creates_a_historic_location() {
    Notification::fake();
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
    Notification::fake();
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $geoAccount = factory(\App\Models\Business\GeoAccount::class)->create();
    $this->createAccounts($geoAccount);

    $attributes = [
      'beacon_identifier' => $geoAccount->location->beaconAccount->identifier,
    ];

    $response = $this->json('POST', "/api/customer/location", $attributes)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_customer_must_post_correct_data() {
    Notification::fake();
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $geoAccount = factory(\App\Models\Business\GeoAccount::class)->create();
    $this->createAccounts($geoAccount);
    $headers = $this->customerHeaders($customer);

    $attributes = [
      'beacon_identifier' => "fcdcdf",
    ];

    $response = $this->json('POST', "/api/customer/location", $attributes)->assertStatus(422);
    $response = $response->getData();
    $this->assertEquals('The beacon identifier must be a valid UUID.', $response->errors->beacon_identifier[0]);
  }

  public function test_an_auth_customer_can_create_an_active_location() {
    Notification::fake();
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $geoAccount = factory(\App\Models\Business\GeoAccount::class)->create();
    $this->createAccounts($geoAccount);
    $headers = $this->customerHeaders($customer);

    $attributes = [
      'beacon_identifier' => $geoAccount->location->beaconAccount->identifier,
    ];

    $response = $this->json('POST', "/api/customer/location", $attributes, $headers)->getData();
    $this->assertDatabaseHas('active_locations', ['identifier' => $response->data->active_location_id, 'customer_id' => $customer->id]);
  }

  public function test_creating_an_active_location_sends_enter_notification() {
    Notification::fake();
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $geoAccount = factory(\App\Models\Business\GeoAccount::class)->create();
    $this->createAccounts($geoAccount);
    $headers = $this->customerHeaders($customer);

    $attributes = [
      'beacon_identifier' => $geoAccount->location->beaconAccount->identifier,
    ];

    $response = $this->json('POST', "/api/customer/location", $attributes, $headers)->getData();

    Notification::assertSentTo(
      [$customer],
      EnterBusiness::class
    );
  }

  public function test_an_unauth_customer_cannot_delete_an_active_location() {
    Notification::fake();
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $geoAccount = factory(\App\Models\Business\GeoAccount::class)->create();
    $this->createAccounts($geoAccount);
    $activeLocation = factory(\App\Models\Location\ActiveLocation::class)->create(['location_id' => $geoAccount->location->id, 'customer_id' =>$customer->id]);

    $response = $this->json('DELETE', "/api/customer/location/{$activeLocation->identifier}")->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_customer_can_delete_an_active_location_no_transaction() {
    Notification::fake();
    $geoAccount = factory(\App\Models\Business\GeoAccount::class)->create();
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['business_id' => $geoAccount->location->business_id, 'type' => 'shopify']);
    $activeLocation = factory(\App\Models\Location\ActiveLocation::class)->create(['location_id' => $geoAccount->location->id, 'transaction_id' => null]);
    $customer = $activeLocation->customer;

    
    $headers = $this->customerHeaders($customer);

    $response = $this->json('DELETE', "/api/customer/location/{$activeLocation->identifier}", $headers)->getData();
    $this->assertTrue($response->data->deleted);
    $this->assertDatabaseMissing('active_locations', ['id' => $activeLocation->id]);
  }

  public function test_deleting_an_active_location_no_transaction_does_not_send_notification() {
    Notification::fake();
    $geoAccount = factory(\App\Models\Business\GeoAccount::class)->create();
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['business_id' => $geoAccount->location->business_id, 'type' => 'shopify']);
    $activeLocation = factory(\App\Models\Location\ActiveLocation::class)->create(['location_id' => $geoAccount->location->id, 'transaction_id' => null]);
    $customer = $activeLocation->customer;

    
    $headers = $this->customerHeaders($customer);

    $response = $this->json('DELETE', "/api/customer/location/{$activeLocation->identifier}", $headers)->getData();

    Notification::assertNothingSent();
  }

  public function test_an_auth_customer_cannot_delete_active_location_with_transaction() {
    Notification::fake();
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $geoAccount = factory(\App\Models\Business\GeoAccount::class)->create();
    $this->createAccounts($geoAccount);
    $transaction = factory(\App\Models\Transaction\Transaction::class)->create(['customer_id' =>$customer->id, 'business_id' => $geoAccount->location->business->id]);
    $activeLocation = factory(\App\Models\Location\ActiveLocation::class)->create(['location_id' => $geoAccount->location->id, 'customer_id' =>$customer->id, 'transaction_id' => $transaction->id]);
    $headers = $this->customerHeaders($customer);

    $response = $this->json('DELETE', "/api/customer/location/{$activeLocation->identifier}", $headers)->getData();
    $this->assertFalse($response->data->deleted);
    $this->assertDatabaseHas('active_locations', ['id' => $activeLocation->id]);
  }

  public function test_deleting_an_active_location_with_transaction_sends_exit_notification() {
    Notification::fake();
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $geoAccount = factory(\App\Models\Business\GeoAccount::class)->create();
    $this->createAccounts($geoAccount);
    $transaction = factory(\App\Models\Transaction\Transaction::class)->create(['customer_id' =>$customer->id, 'business_id' => $geoAccount->location->business->id]);
    $activeLocation = factory(\App\Models\Location\ActiveLocation::class)->create(['location_id' => $geoAccount->location->id, 'customer_id' =>$customer->id, 'transaction_id' => $transaction->id]);
    $headers = $this->customerHeaders($customer);

    $response = $this->json('DELETE', "/api/customer/location/{$activeLocation->identifier}", $headers)->getData();

    Notification::assertSentTo(
      [$customer],
      ExitBusiness::class
    );
  }

  public function test_deleting_active_location_with_error_status_sends_fix_bill_notification() {
    Notification::fake();
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $geoAccount = factory(\App\Models\Business\GeoAccount::class)->create();
    $this->createAccounts($geoAccount);

    $transaction = factory(\App\Models\Transaction\Transaction::class)->create(['customer_id' =>$customer->id, 'business_id' => $geoAccount->location->business->id]);
    $transaction->updateStatus(500);


    $activeLocation = factory(\App\Models\Location\ActiveLocation::class)->create(['location_id' => $geoAccount->location->id, 'customer_id' =>$customer->id, 'transaction_id' => $transaction->id]);
    $headers = $this->customerHeaders($customer);

    $response = $this->json('DELETE', "/api/customer/location/{$activeLocation->identifier}", $headers)->getData();

    Notification::assertSentTo(
      [$customer],
      FixBill::class
    );
  }

  public function test_deleting_an_active_location_with_transaction_changes_transction_status() {
    Notification::fake();
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $geoAccount = factory(\App\Models\Business\GeoAccount::class)->create();
    $this->createAccounts($geoAccount);
    $transaction = factory(\App\Models\Transaction\Transaction::class)->create(['customer_id' =>$customer->id, 'business_id' => $geoAccount->location->business->id]);
    $activeLocation = factory(\App\Models\Location\ActiveLocation::class)->create(['location_id' => $geoAccount->location->id, 'customer_id' =>$customer->id, 'transaction_id' => $transaction->id]);
    $headers = $this->customerHeaders($customer);

    $response = $this->json('DELETE', "/api/customer/location/{$activeLocation->identifier}", $headers)->getData();

    $this->assertEquals(105, $transaction->fresh()->status->code);
  }

  public function test_a_creating_an_active_location_creates_bill_identifier_square() {
    Notification::fake();
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $geoAccount = factory(\App\Models\Business\GeoAccount::class)->create();
    $this->createAccounts($geoAccount);
    $headers = $this->customerHeaders($customer);

    $attributes = [
      'beacon_identifier' => $geoAccount->location->beaconAccount->identifier,
    ];

    $response = $this->json('POST', "/api/customer/location", $attributes, $headers)->getData();
    $this->assertDatabaseHas('active_locations', ['bill_identifier' => 'JDKYHBWT1D4F8MFH63DBMEN8Y4']);
  }

  public function test_updating_active_location_with_keep_open_status_changes_status_to_open() {
    Notification::fake();
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $geoAccount = factory(\App\Models\Business\GeoAccount::class)->create();
    $this->createAccounts($geoAccount);
    $headers = $this->customerHeaders($customer);

    $transaction = factory(\App\Models\Transaction\Transaction::class)->create(['customer_id' =>$customer->id, 'business_id' => $geoAccount->location->business->id]);
    $activeLocation = factory(\App\Models\Location\ActiveLocation::class)->create(['location_id' => $geoAccount->location->id, 'customer_id' => $customer->id, 'transaction_id' => $transaction->id]);
    $transaction->updateStatus(106);

    $attributes = [
      'beacon_identifier' => $geoAccount->location->beaconAccount->identifier,
    ];

    $response = $this->json('POST', "/api/customer/location", $attributes, $headers)->getData();
    $this->assertEquals(100, $transaction->fresh()->status->code);
  }

  public function test_updating_active_location_with_fix_error_warnings_resets_error_warnings() {
    Notification::fake();
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $geoAccount = factory(\App\Models\Business\GeoAccount::class)->create();
    $this->createAccounts($geoAccount);
    $headers = $this->customerHeaders($customer);

    $transaction = factory(\App\Models\Transaction\Transaction::class)->create(['customer_id' =>$customer->id, 'business_id' => $geoAccount->location->business->id]);
    $activeLocation = factory(\App\Models\Location\ActiveLocation::class)->create(['location_id' => $geoAccount->location->id, 'customer_id' => $customer->id, 'transaction_id' => $transaction->id]);
    $transaction->updateStatus(500);
    TransactionNotification::storeNewNotification($transaction->identifier, 'fix_bill');
    $transaction->notification->addWarningSent();

    $attributes = [
      'beacon_identifier' => $geoAccount->location->beaconAccount->identifier,
    ];

    $this->assertTrue($transaction->notification->fix_bill_sent);
    $this->assertNotNull($transaction->notification->time_fix_bill_sent);
    $this->assertEquals(1, $transaction->notification->number_times_fix_bill_sent);
    $response = $this->json('POST', "/api/customer/location", $attributes, $headers)->getData();
    $this->assertFalse($transaction->fresh()->notification->fix_bill_sent);
    $this->assertNull($transaction->fresh()->notification->time_fix_bill_sent);
    $this->assertEquals(0, $transaction->fresh()->notification->number_times_fix_bill_sent);
  }



  private function createAccounts($geoAccount) {
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['business_id' => $geoAccount->location->business_id]);
    $payFacAccount = factory(\App\Models\Business\PayFacAccount::class)->create(['account_id' => $geoAccount->location->business->account->id]);
    $payFacBusinessAccount = factory(\App\Models\Business\PayFacBusiness::class)->create(['pay_fac_account_id' => $payFacAccount->id]);
    factory(\App\Models\Business\SquareAccount::class)->create(['pos_account_id' => $posAccount->id]);
  }
}
