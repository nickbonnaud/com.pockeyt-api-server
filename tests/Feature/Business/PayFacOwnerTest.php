<?php

namespace Tests\Feature\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PayFacOwnerTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unauthorized_business_cannot_store_owner_data() {
    $payFacOwner = factory(\App\Models\Business\PayFacOwner::class)->make();
    $payFacOwner['ssn'] = $this->faker->ssn;

    $response = $this->json('POST', '/api/business/payfac/owner', $payFacOwner->toArray())->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_authorized_business_must_have_less_than_equal_to_100_ownership() {
  	$payFacOwner = factory(\App\Models\Business\PayFacOwner::class)->make(['percent_ownership' => 101]);
  	$business = $payFacOwner->payFacAccount->account->business;
  	$header = $this->businessHeaders($business);
  	$payFacOwner = $payFacOwner->toArray();
  	$payFacOwner['ssn'] = $this->faker->ssn;

  	$response = $this->json('POST', '/api/business/payfac/owner', $payFacOwner, $header)->assertStatus(422);
  	$response = $response->getData();
		$this->assertEquals('The given data was invalid.', $response->message);
		$this->assertEquals('Percent ownership is greater than 100.', $response->errors->percent_ownership[0]);
  }

  public function test_an_authorized_business_can_store_owner_data() {
    factory(\App\Models\Business\AccountStatus::class)->create();
    $business = factory(\App\Models\Business\Business::class)->create();
    $account = $business->account;
    $payFacAccount = factory(\App\Models\Business\PayFacAccount::class)->create(['account_id' => $account->id]);
    $payFacOwner = factory(\App\Models\Business\PayFacOwner::class)->make();
    $header = $this->businessHeaders($business);
    $payFacOwner = $payFacOwner->toArray();
    $payFacOwner['ssn'] = $this->faker->numerify('#########');
    $payFacOwner['dob'] = '10/24/1987';
    $response = $this->json('POST', '/api/business/payfac/owner', $payFacOwner, $header)->getData();

    $this->assertDatabaseHas('pay_fac_owners', ['id' => $business->fresh()->account->getPayFacOwners()->first()->id]);

    $this->assertEquals($payFacOwner['last_name'], $response->data->last_name);
  }

  public function test_an_authorized_business_can_store_multiple_owners() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $account = $business->account;
    $payFacAccount = factory(\App\Models\Business\PayFacAccount::class)->create(['account_id' => $account->id]);
    factory(\App\Models\Business\PayFacOwner::class)->create(['pay_fac_account_id' => $payFacAccount->id, 'percent_ownership' => 25]);

    $payFacOwner = factory(\App\Models\Business\PayFacOwner::class)->make(['percent_ownership' => 25]);
    $header = $this->businessHeaders($business);
    $payFacOwnerArray = $payFacOwner->toArray();
    $payFacOwnerArray['ssn'] = $this->faker->numerify("#########");

    $response = $this->json('POST', '/api/business/payfac/owner', $payFacOwnerArray, $header)->getData();

    $this->assertDatabaseHas('pay_fac_owners', ['first_name' => $payFacOwner->first_name, 'last_name' => $payFacOwner->last_name]);
  }

  public function test_an_authorized_business_cannot_store_multiple_owners_above_hundred() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $account = $business->account;
    $payFacAccount = factory(\App\Models\Business\PayFacAccount::class)->create(['account_id' => $account->id]);
    factory(\App\Models\Business\PayFacOwner::class)->create(['pay_fac_account_id' => $payFacAccount->id, 'percent_ownership' => 75]);

    $payFacOwner = factory(\App\Models\Business\PayFacOwner::class)->make(['percent_ownership' => 50]);
    $header = $this->businessHeaders($business);
    $payFacOwnerArray = $payFacOwner->toArray();
    $payFacOwnerArray['ssn'] = $this->faker->ssn;

    $response = $this->json('POST', '/api/business/payfac/owner', $payFacOwnerArray, $header)->assertStatus(422);
    $response = $response->getData();
    $this->assertEquals('The given data was invalid.', $response->message);
    $this->assertEquals('Percent ownership is greater than 100.', $response->errors->percent_ownership[0]);
  }

  public function test_an_authorized_business_can_update_their_owner_data() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $account = $business->account;
    $payFacAccount = factory(\App\Models\Business\PayFacAccount::class)->create(['account_id' => $account->id]);

    $payFacOwner = factory(\App\Models\Business\PayFacOwner::class)->create(['pay_fac_account_id' => $payFacAccount->id]);
    $header = $this->businessHeaders($business);

    $payFacOwnerArray = $payFacOwner->toArray();
    $lastName = "Newname";
    $payFacOwnerArray['last_name'] = $lastName;
    $payFacOwnerArray['ssn'] = $payFacOwner->ssn;
    $payFacOwnerArray['title'] = 'CEO';

    $this->assertDatabaseHas('pay_fac_owners', ['id' => $payFacOwner->id, 'last_name' => $payFacOwner->last_name]);

    $response = $this->json('PATCH', "/api/business/payfac/owner/{$payFacOwner->identifier}", $payFacOwnerArray, $header)->getData();

    $this->assertDatabaseHas('pay_fac_owners', ['id' => $payFacOwner->id, 'last_name' => $lastName]);

    $this->assertEquals($lastName, $response->data->last_name);
  }

  public function test_an_authorized_business_cannot_update_owners_above_hundred() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $account = $business->account;
    $payFacAccount = factory(\App\Models\Business\PayFacAccount::class)->create(['account_id' => $account->id]);

    $payFacOwner = factory(\App\Models\Business\PayFacOwner::class)->create(['pay_fac_account_id' => $payFacAccount->id, 'percent_ownership' => 50]);

    factory(\App\Models\Business\PayFacOwner::class)->create(['pay_fac_account_id' => $payFacAccount->id, 'percent_ownership' => 50]);
    $header = $this->businessHeaders($business);

    $payFacOwnerArray = $payFacOwner->toArray();
    $lastName = "Newname";
    $payFacOwnerArray['last_name'] = $lastName;
    $payFacOwnerArray['ssn'] = $payFacOwner->ssn;
    $payFacOwnerArray['percent_ownership'] = 75;
    $payFacOwnerArray['title'] = 'CEO';

    $response = $this->json('PATCH', "/api/business/payfac/owner/{$payFacOwner->identifier}", $payFacOwnerArray, $header)->assertStatus(422);
    $response = $response->getData();
    $this->assertEquals('The given data was invalid.', $response->message);
    $this->assertEquals('Percent ownership is greater than 100.', $response->errors->percent_ownership[0]);
  }

  public function test_an_ssn_authorized_business_can_update_their_owner_data() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $account = $business->account;
    $payFacAccount = factory(\App\Models\Business\PayFacAccount::class)->create(['account_id' => $account->id]);

    $payFacOwner = factory(\App\Models\Business\PayFacOwner::class)->create(['pay_fac_account_id' => $payFacAccount->id]);
    $header = $this->businessHeaders($business);

    $payFacOwnerArray = $payFacOwner->toArray();
    $lastName = "Newname";
    $payFacOwnerArray['last_name'] = $lastName;
    $payFacOwnerArray['ssn'] = 'XXXXX'. substr($payFacOwner->ssn, -4);
    $payFacOwnerArray['title'] = 'CEO';

    $this->assertDatabaseHas('pay_fac_owners', ['id' => $payFacOwner->id, 'last_name' => $payFacOwner->last_name]);

    $response = $this->json('PATCH', "/api/business/payfac/owner/{$payFacOwner->identifier}", $payFacOwnerArray, $header)->getData();

    $this->assertDatabaseHas('pay_fac_owners', ['id' => $payFacOwner->id, 'last_name' => $lastName]);

    $this->assertEquals($lastName, $response->data->last_name);
  }

  public function test_an_ssn_is_not_changed_if_left_untouched() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $account = $business->account;
    $payFacAccount = factory(\App\Models\Business\PayFacAccount::class)->create(['account_id' => $account->id]);

    $payFacOwner = factory(\App\Models\Business\PayFacOwner::class)->create(['pay_fac_account_id' => $payFacAccount->id]);
    $header = $this->businessHeaders($business);

    $payFacOwnerArray = $payFacOwner->toArray();
    $lastName = "Newname";
    $payFacOwnerArray['last_name'] = $lastName;
    $oldSsn = $payFacOwner->ssn;
    $payFacOwnerArray['ssn'] = 'XXXXX'. substr($payFacOwner->ssn, -4);
    $payFacOwnerArray['title'] = 'CEO';

    $this->assertDatabaseHas('pay_fac_owners', ['id' => $payFacOwner->id, 'last_name' => $payFacOwner->last_name]);

    $response = $this->json('PATCH', "/api/business/payfac/owner/{$payFacOwner->identifier}", $payFacOwnerArray, $header)->getData();

    $this->assertDatabaseHas('pay_fac_owners', ['id' => $payFacOwner->id, 'last_name' => $lastName]);

    $this->assertEquals($oldSsn, $payFacOwner->fresh()->ssn);
  }

  public function test_an_ssn_is_changed_if_changed() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $account = $business->account;
    $payFacAccount = factory(\App\Models\Business\PayFacAccount::class)->create(['account_id' => $account->id]);

    $payFacOwner = factory(\App\Models\Business\PayFacOwner::class)->create(['pay_fac_account_id' => $payFacAccount->id]);
    $header = $this->businessHeaders($business);

    $payFacOwnerArray = $payFacOwner->toArray();
    $lastName = "Newname";
    $payFacOwnerArray['last_name'] = $lastName;
    $oldSsn = $payFacOwner->ssn;
    $payFacOwnerArray['ssn'] = $this->faker->numerify("#########");
    $payFacOwnerArray['title'] = 'CEO';

    $this->assertDatabaseHas('pay_fac_owners', ['id' => $payFacOwner->id, 'last_name' => $payFacOwner->last_name]);

    $response = $this->json('PATCH', "/api/business/payfac/owner/{$payFacOwner->identifier}", $payFacOwnerArray, $header)->getData();

    $this->assertDatabaseHas('pay_fac_owners', ['id' => $payFacOwner->id, 'last_name' => $lastName]);

    $this->assertNotEquals($oldSsn, $payFacOwner->fresh()->ssn);
  }

  public function test_an_unauth_business_cannot_destroy_an_owner() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $account = $business->account;
    $payFacAccount = factory(\App\Models\Business\PayFacAccount::class)->create(['account_id' => $account->id]);

    $payFacOwner = factory(\App\Models\Business\PayFacOwner::class)->create(['pay_fac_account_id' => $payFacAccount->id]);

    $this->assertDatabaseHas('pay_fac_owners', ['id' => $payFacOwner->id, 'last_name' => $payFacOwner->last_name]);

    $response = $this->json('DELETE', "/api/business/payfac/owner/{$payFacOwner->identifier}")->assertStatus(401);

    $this->assertDatabaseHas('pay_fac_owners', ['id' => $payFacOwner->id, 'last_name' => $payFacOwner->last_name]);

    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_a_business_can_only_delete_their_owners() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $account = $business->account;
    $payFacAccount = factory(\App\Models\Business\PayFacAccount::class)->create(['account_id' => $account->id]);

    $payFacOwner = factory(\App\Models\Business\PayFacOwner::class)->create(['pay_fac_account_id' => $payFacAccount->id]);
    $this->businessHeaders($business);

    $otherBusinessOwner = factory(\App\Models\Business\PayFacOwner::class)->create();

    $this->assertDatabaseHas('pay_fac_owners', ['id' => $otherBusinessOwner->id, 'last_name' => $otherBusinessOwner->last_name]);

    $response = $this->json('DELETE', "/api/business/payfac/owner/{$otherBusinessOwner->identifier}")->assertStatus(403);

    $this->assertDatabaseHas('pay_fac_owners', ['id' => $otherBusinessOwner->id, 'last_name' => $otherBusinessOwner->last_name]);


    $this->assertEquals('Permission denied.', $response->getData()->errors);
  }

  public function test_a_business_can_delete_an_owner() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $account = $business->account;
    $payFacAccount = factory(\App\Models\Business\PayFacAccount::class)->create(['account_id' => $account->id]);

    $payFacOwner = factory(\App\Models\Business\PayFacOwner::class)->create(['pay_fac_account_id' => $payFacAccount->id, 'primary' => false]);

    factory(\App\Models\Business\PayFacOwner::class, 3)->create(['pay_fac_account_id' => $payFacAccount->id]);
    $this->businessHeaders($business);

    $this->assertDatabaseHas('pay_fac_owners', ['id' => $payFacOwner->id, 'last_name' => $payFacOwner->last_name]);
    $this->assertEquals(\App\Models\Business\PayFacOwner::count(), 4);

    $response = $this->json('DELETE', "/api/business/payfac/owner/{$payFacOwner->identifier}")->getData();

    $this->assertEquals(true, $response->success);
    $this->assertDatabaseMissing('pay_fac_owners', ['id' => $payFacOwner->id, 'last_name' => $payFacOwner->last_name]);
    $this->assertEquals(\App\Models\Business\PayFacOwner::count(), 3);
  }

  public function test_a_business_cannot_delete_a_primary_owner() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $account = $business->account;
    $payFacAccount = factory(\App\Models\Business\PayFacAccount::class)->create(['account_id' => $account->id]);

    $payFacOwner = factory(\App\Models\Business\PayFacOwner::class)->create(['pay_fac_account_id' => $payFacAccount->id, 'primary' => true]);

    factory(\App\Models\Business\PayFacOwner::class, 3)->create(['pay_fac_account_id' => $payFacAccount->id]);
    $this->businessHeaders($business);

    $this->assertDatabaseHas('pay_fac_owners', ['id' => $payFacOwner->id, 'last_name' => $payFacOwner->last_name]);
    $this->assertEquals(\App\Models\Business\PayFacOwner::count(), 4);

    $response = $this->json('DELETE', "/api/business/payfac/owner/{$payFacOwner->identifier}")->assertStatus(403);

    $this->assertEquals("Cannot delete primary owner.", $response->getData()->errors);
    $this->assertDatabaseHas('pay_fac_owners', ['id' => $payFacOwner->id, 'last_name' => $payFacOwner->last_name]);
    $this->assertEquals(\App\Models\Business\PayFacOwner::count(), 4);
  }
}
