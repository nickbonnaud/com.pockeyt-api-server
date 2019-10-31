<?php

namespace Tests\Feature\Business;

use Tests\TestCase;
use Illuminate\Support\Arr;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PayFacBusinessTest extends TestCase {
	use WithFaker, RefreshDatabase;

	public function test_an_unauthorized_business_cannot_store_business_data() {
		factory(\App\Models\Business\AccountStatus::class)->create();
		$payFacBusiness = factory(\App\Models\Business\PayFacBusiness::class)->make();

		$response = $this->json('POST', '/api/business/payfac/business', $payFacBusiness->toArray())->assertStatus(401);
		$this->assertEquals('Unauthenticated.', ($response->getData())->message);
	}

	public function test_an_authenticated_business_requires_the_correct_data() {
		factory(\App\Models\Business\AccountStatus::class)->create();
		$business = factory(\App\Models\Business\Business::class)->create();
		$header = $this->businessHeaders($business);
		$response = $this->json('POST', '/api/business/payfac/business', [], $header)->assertStatus(422);
		$response = $response->getData();

		$this->assertEquals('The given data was invalid.', $response->message);
	}

	public function test_an_ein_is_required_if_business_not_sole_prop() {
		factory(\App\Models\Business\AccountStatus::class)->create();
		$payFacBusiness = Arr::except((factory(\App\Models\Business\PayFacBusiness::class)->make())->toArray(), ['ein']);
		$payFacBusiness['entity_type'] = 'llc';
		$business = factory(\App\Models\Business\Business::class)->create();
		$header = $this->businessHeaders($business);
		$response = $this->json('POST', '/api/business/payfac/business', $payFacBusiness, $header)->getData();
		$this->assertEquals('The given data was invalid.', $response->message);
		$this->assertEquals('The ein field is required unless entity type is in soleProprietorship.', $response->errors->ein[0]);

		$payFacBusiness['ein'] = $this->faker->ein;
		$response = $this->json('POST', '/api/business/payfac/business', $payFacBusiness, $header)->assertStatus(200);
	}

	public function test_an_ein_is_not_required_if_business_a_sole_prop() {
		factory(\App\Models\Business\AccountStatus::class)->create();
		$payFacBusiness = Arr::except((factory(\App\Models\Business\PayFacBusiness::class)->make())->toArray(), ['ein']);
		$payFacBusiness['entity_type'] = 'soleProprietorship';
		$business = factory(\App\Models\Business\Business::class)->create();
		$header = $this->businessHeaders($business);
		$response = $this->json('POST', '/api/business/payfac/business', $payFacBusiness, $header)->assertStatus(200);
	}

	public function test_an_authorized_business_can_store_pay_fac_business_data() {
		factory(\App\Models\Business\AccountStatus::class)->create();
		$payFacBusiness = (factory(\App\Models\Business\PayFacBusiness::class)->make())->toArray();
		$payFacBusiness['entity_type'] = 'soleProprietorship';
		$business = factory(\App\Models\Business\Business::class)->create();
		$header = $this->businessHeaders($business);
		$response = $this->json('POST', '/api/business/payfac/business', $payFacBusiness, $header)->getData();
		$this->assertDatabaseHas('accounts', ['id' => $business->account->id]);
		$this->assertDatabaseHas('pay_fac_accounts', ['id' => $business->account->payFacAccount->id]);
		$this->assertDatabaseHas('pay_fac_businesses', ['id' => $business->account->getPayFacBusiness()->id]);

		$this->assertEquals($payFacBusiness['business_name'], $response->data->business_name);
	}

	public function test_an_unauth_business_cannot_update_their_pay_fac_data() {
		factory(\App\Models\Business\AccountStatus::class)->create();
		$payFacBusiness = factory(\App\Models\Business\PayFacBusiness::class)->create();

		$payFacBusiness = $payFacBusiness->toArray();
		$payFacBusiness['entity_type'] = 'soleProprietorship';
		$newBusinessName = "New Name";
		$payFacBusiness['business_name'] = $newBusinessName;

		$response = $this->json('PATCH', "/api/business/payfac/business/{$payFacBusiness['identifier']}", $payFacBusiness)->assertStatus(401);
		$this->assertEquals('Unauthenticated.', ($response->getData())->message);
	}

	public function test_auth_business_can_update_their_pay_fac_data() {
		factory(\App\Models\Business\AccountStatus::class)->create();
		$payFacBusiness = factory(\App\Models\Business\PayFacBusiness::class)->create();
		$business = $payFacBusiness->payFacAccount->account->business;
		$header = $this->businessHeaders($business);

		$payFacBusiness = $payFacBusiness->toArray();
		$payFacBusiness['entity_type'] = 'soleProprietorship';
		$newBusinessName = "New Name";
		$payFacBusiness['business_name'] = $newBusinessName;

		$response = $this->json('PATCH', "/api/business/payfac/business/{$payFacBusiness['identifier']}", $payFacBusiness, $header)->getData();

		$this->assertDatabaseHas('pay_fac_businesses', ['business_name' => $business->account->getPayFacBusiness()->business_name]);
		$this->assertEquals($newBusinessName, $response->data->business_name);
	}

	public function test_auth_business_can_update_their_entity_type() {
		factory(\App\Models\Business\AccountStatus::class)->create();
		$payFacBusiness = factory(\App\Models\Business\PayFacBusiness::class)->create();
		$business = $payFacBusiness->payFacAccount->account->business;
		$header = $this->businessHeaders($business);

		$this->assertDatabaseHas('pay_fac_accounts', ['id' => $payFacBusiness->payFacAccount->id, 'entity_type' => $payFacBusiness->payFacAccount->entity_type]);

		$payFacBusinessArray = $payFacBusiness->toArray();
		$payFacBusinessArray['entity_type'] = 'partnership';

		$response = $this->json('PATCH', "/api/business/payfac/business/{$payFacBusiness['identifier']}", $payFacBusinessArray, $header)->getData();

		$this->assertDatabaseHas('pay_fac_accounts', ['id' => $payFacBusiness->payFacAccount->id, 'entity_type' =>'Business']);

		$this->assertEquals('Business', $response->data->entity_type);
	}
}
