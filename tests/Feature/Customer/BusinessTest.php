<?php

namespace Tests\Feature\Customer;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BusinessTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unauth_customer_cannot_fetch_businesses() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $profiles = factory(\App\Models\Business\Profile::class, 10)->create();

    $response = $this->json('GET', 'api/customer/business?name=fake')->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_customer_can_fetch_businesses_by_name() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $headers = $this->customerHeaders($customer);
    $i = 0;
    while ($i <= 10) {
      $profile = factory(\App\Models\Business\Profile::class)->create();
      factory(\App\Models\Business\Location::class)->create(['business_id' => $profile->business_id]);
      $i++;
    }

    $response = $this->json('GET', "api/customer/business?name={$profile->name}")->getData();
    $this->assertEquals($profile->name, $response->data[0]->profile->name);
  }

  public function test_an_auth_customer_is_returned_empty_array_if_no_matches() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $headers = $this->customerHeaders($customer);
    $i = 0;
    while ($i <= 10) {
      $profile = factory(\App\Models\Business\Profile::class)->create();
      factory(\App\Models\Business\Location::class)->create(['business_id' => $profile->business_id]);
      $i++;
    }

    $response = $this->json('GET', "api/customer/business?name=fake_name")->getData();
    $this->assertEquals(0, count($response->data));
  }

  public function test_an_auth_customer_can_fetch_business_by_beacon_id() {
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $headers = $this->customerHeaders($customer);

    $geoAccount = factory(\App\Models\Business\GeoAccount::class)->create();
    $beaconAccount = $geoAccount->location->beaconAccount;
    $profile = factory(\App\Models\Business\Profile::class)->create(['business_id' => $beaconAccount->location->business->id]);

    $response = $this->json('GET', "api/customer/business?beacon={$beaconAccount->identifier}")->getData();

    $this->assertEquals($response->data[0]->location->beacon->identifier, $beaconAccount->location->identifier);
  }
}
