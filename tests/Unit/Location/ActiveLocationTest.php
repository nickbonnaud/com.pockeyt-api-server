<?php

namespace Tests\Unit\Location;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActiveLocationTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_active_location_belongs_to_a_customer() {
  	$customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $geoAccount = $this->createAccounts();
  	$activeLocation = factory(\App\Models\Location\ActiveLocation::class)->create(['customer_id' => $customer->id, 'location_id' => $geoAccount->location_id]);
  	$this->assertInstanceOf('App\Models\Location\ActiveLocation', $customer->activeLocations->first());
  }

  public function test_a_customer_has_many_active_locations() {
  	$customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $i = 0;
    while ($i < 3) {
      $geoAccount = $this->createAccounts();
      $activeLocation = factory(\App\Models\Location\ActiveLocation::class)->create(['customer_id' => $customer->id, 'location_id' => $geoAccount->location_id]);
      $i++;
    }
  	$this->assertEquals(3, $customer->activeLocations->count());
  }

  public function test_an_active_location_has_one_customer() {
  	$customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $geoAccount = $this->createAccounts();
    $activeLocation = factory(\App\Models\Location\ActiveLocation::class)->create(['customer_id' => $customer->id, 'location_id' => $geoAccount->location_id]);
  	$this->assertInstanceOf('App\Models\Customer\Customer', $activeLocation->customer);
  }

  public function test_an_active_customer_belongs_to_a_location() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $geoAccount = $this->createAccounts();
    $activeCustomer = factory(\App\Models\Location\ActiveLocation::class)->create(['customer_id' => $customer->id, 'location_id' => $geoAccount->location_id]);
  	$location = $geoAccount->location;
  	$this->assertInstanceOf('App\Models\Location\ActiveLocation', $location->activeCustomers->first());
  }

  public function test_a_location_has_many_active_customers() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $geoAccount = $this->createAccounts();

    $i = 0;
    while ($i < 5) {
      $activeCustomer = factory(\App\Models\Location\ActiveLocation::class)->create(['customer_id' => $customer->id, 'location_id' => $geoAccount->location_id]);
      $i++;
    }
    $location = $geoAccount->location;

  	$this->assertEquals(5, $location->activeCustomers->count());
  }

  public function test_an_active_customer_has_one_location() {
  	$customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $geoAccount = $this->createAccounts();
    $activeCustomer = factory(\App\Models\Location\ActiveLocation::class)->create(['customer_id' => $customer->id, 'location_id' => $geoAccount->location_id]);
  	$this->assertInstanceOf('App\Models\Business\Location', $activeCustomer->location);
  }

  public function test_an_active_customer_location_belongs_to_a_notification() {
  	$notification = factory(\App\Models\Transaction\TransactionNotification::class)->create();

    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $geoAccount = $this->createAccounts();
    $activeCustomer = factory(\App\Models\Location\ActiveLocation::class)->create(['customer_id' => $customer->id, 'location_id' => $geoAccount->location_id, 'transaction_notification_id' => $notification->id]);

  	$this->assertInstanceOf('App\Models\Location\ActiveLocation', $notification->activeCustomerLocation);
  }

  public function test_an_active_customer_location_has_one_notification() {
  	$notification = factory(\App\Models\Transaction\TransactionNotification::class)->create();
  	$customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $geoAccount = $this->createAccounts();
    $activeCustomerLocation = factory(\App\Models\Location\ActiveLocation::class)->create(['customer_id' => $customer->id, 'location_id' => $geoAccount->location_id, 'transaction_notification_id' => $notification->id]);
  	$this->assertInstanceOf('App\Models\Transaction\TransactionNotification', $activeCustomerLocation->notification);
  }

  public function test_an_active_customer_location_belongs_to_a_transaction() {
  	$transaction = factory(\App\Models\Transaction\Transaction::class)->create();
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $geoAccount = $this->createAccounts();
    $activeCustomerLocation = factory(\App\Models\Location\ActiveLocation::class)->create(['customer_id' => $customer->id, 'location_id' => $geoAccount->location_id, 'transaction_id' => $transaction->id]);
  	$this->assertInstanceOf('App\Models\Location\ActiveLocation', $transaction->activeCustomerLocation);
  }

  public function test_an_active_customer_location_has_one_transaction() {
  	$transaction = factory(\App\Models\Transaction\Transaction::class)->create();
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $geoAccount = $this->createAccounts();
    $activeCustomerLocation = factory(\App\Models\Location\ActiveLocation::class)->create(['customer_id' => $customer->id, 'location_id' => $geoAccount->location_id, 'transaction_id' => $transaction->id]);
  	$this->assertInstanceOf('App\Models\Transaction\Transaction', $activeCustomerLocation->transaction);
  }




  private function createAccounts() {
    $geoAccount = factory(\App\Models\Business\GeoAccount::class)->create();
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['business_id' => $geoAccount->location->business_id]);
    $account = $geoAccount->location->business->account;
    $payFacAccount = factory(\App\Models\Business\PayFacAccount::class)->create(['account_id' => $account->id]);
    $payFacBusinessAccount = factory(\App\Models\Business\PayFacBusiness::class)->create(['pay_fac_account_id' => $payFacAccount->id]);
    factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'closed']);
    factory(\App\Models\Business\SquareAccount::class)->create(['pos_account_id' => $posAccount->id]);
    return $geoAccount;
  }
}
