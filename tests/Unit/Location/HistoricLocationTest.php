<?php

namespace Tests\Unit\Location;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HistoricLocationTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function test_an_historic_location_belongs_to_a_customer() {
  	$customer = factory(\App\Models\Customer\Customer::class)->create();
  	$historicLocation = factory(\App\Models\Location\HistoricLocation::class)->create(['customer_id' => $customer->id]);
  	$this->assertInstanceOf('App\Models\Location\HistoricLocation', $customer->historicLocations->first());
  }

  public function test_a_customer_has_many_historic_locations() {
  	$customer = factory(\App\Models\Customer\Customer::class)->create();
  	$historicLocation = factory(\App\Models\Location\HistoricLocation::class, 3)->create(['customer_id' => $customer->id]);
  	$this->assertEquals(3, $customer->historicLocations->count());
  }

  public function test_an_historic_location_has_one_customer() {
  	$customer = factory(\App\Models\Customer\Customer::class)->create();
  	$historicLocation = factory(\App\Models\Location\HistoricLocation::class)->create(['customer_id' => $customer->id]);
  	$this->assertInstanceOf('App\Models\Customer\Customer', $historicLocation->customer);
  }

  public function test_an_historic_customer_belongs_to_a_location() {
  	$location = factory(\App\Models\Business\Location::class)->create();
  	$historicCustomer = factory(\App\Models\Location\HistoricLocation::class)->create(['location_id' => $location->id]);
  	$this->assertInstanceOf('App\Models\Location\HistoricLocation', $location->historicCustomers->first());
  }

  public function test_a_location_has_many_historic_customers() {
  	$location = factory(\App\Models\Business\Location::class)->create();
  	$historicCustomer = factory(\App\Models\Location\HistoricLocation::class, 5)->create(['location_id' => $location->id]);
  	$this->assertEquals(5, $location->historicCustomers->count());
  }

  public function test_an_historic_customer_has_one_location() {
  	$location = factory(\App\Models\Business\Location::class)->create();
  	$historicCustomer = factory(\App\Models\Location\HistoricLocation::class)->create(['location_id' => $location->id]);
  	$this->assertInstanceOf('App\Models\Business\Location', $historicCustomer->location);
  }

  public function test_an_historic_customer_location_belongs_to_a_notification() {
  	$notification = factory(\App\Models\Transaction\TransactionNotification::class)->create();
  	$historicCustomerLocation = factory(\App\Models\Location\HistoricLocation::class)->create(['transaction_notification_id' => $notification->id]);
  	$this->assertInstanceOf('App\Models\Location\HistoricLocation', $notification->historicCustomerLocation);
  }

  public function test_an_historic_customer_location_has_one_notification() {
  	$notification = factory(\App\Models\Transaction\TransactionNotification::class)->create();
  	$historicCustomerLocation = factory(\App\Models\Location\HistoricLocation::class)->create(['transaction_notification_id' => $notification->id]);
  	$this->assertInstanceOf('App\Models\Transaction\TransactionNotification', $historicCustomerLocation->notification);
  }

  public function test_an_historic_customer_location_belongs_to_a_transaction() {
  	$transaction = factory(\App\Models\Transaction\Transaction::class)->create();
  	$historicCustomerLocation = factory(\App\Models\Location\HistoricLocation::class)->create(['transaction_id' => $transaction->id]);
  	$this->assertInstanceOf('App\Models\Location\HistoricLocation', $transaction->historicCustomerLocation);
  }

  public function test_an_historic_customer_location_has_one_transaction() {
  	$transaction = factory(\App\Models\Transaction\Transaction::class)->create();
  	$historicCustomerLocation = factory(\App\Models\Location\HistoricLocation::class)->create(['transaction_id' => $transaction->id]);
  	$this->assertInstanceOf('App\Models\Transaction\Transaction', $historicCustomerLocation->transaction);
  }
}
