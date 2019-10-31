<?php

namespace Tests\Feature\Business;

use Tests\TestCase;
use App\Models\Location\ActiveLocation;
use Illuminate\Foundation\Testing\WithFaker;
use App\Helpers\VendTestHelpers as TestHelpers;
use Illuminate\Foundation\Testing\RefreshDatabase;

class VendAccountTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function test_vend_oauth_redirects_to_login_if_not_logged_in() {
    $status = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id]);
    $url = '/api/business/pos/vend/oauth';
    $this->get($url)->assertRedirect(config('urls.dashboard.base') . '/auth/login');
  }

  public function test_vend_oauth_redirects_to_correct_url_with_correct_data() {
    $status = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id]);
    $url = '/api/business/pos/vend/oauth';
    $this->businessHeaders($account->business);
    $response = $this->get($url);


    $clientId = env("VEND_CLIENT_ID");
    $redirectUrl = url('/api/business/pos/vend/oauth');
    $state = auth('business')->getToken();
    $redirectUrl = "https://secure.vendhq.com/connect?response_type=code&client_id={$clientId}&redirect_uri={$redirectUrl}&state={$state}";
    $response->assertRedirect($redirectUrl);
  }

  public function test_vend_return_redirect_with_access_code_must_have_state() {
    $status = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id]);
    $code="access_token";
    $domainPrefix = 'test-domain';

    $url = "/api/business/pos/vend/oauth?code={$code}&domain_prefix={$domainPrefix}";
    $this->get($url)->assertRedirect(config('urls.dashboard.base') . '?oauth=fail');
  }

  public function test_vend_return_redirect_with_access_code_must_have_valid_state() {
    $status = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id]);
    $this->businessHeaders($account->business);

    $code="access_token";
    $domainPrefix = 'test-domain';
    $state = auth('business')->getToken();

    $url = "/api/business/pos/vend/oauth?code={$code}&domain_prefix={$domainPrefix}&state={$state}4325";
    $response = $this->get($url)->assertRedirect(config('urls.dashboard.base') . '?oauth=fail');
  }

  public function test_vend_return_redirect_with_access_code_must_have_domain_prefix() {
    $status = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id]);
    $this->businessHeaders($account->business);

    $code="access_token";
    $domainPrefix = 'test-domain';
    $state = auth('business')->getToken();

    $url = "/api/business/pos/vend/oauth?code={$code}&state={$state}";
    $response = $this->get($url)->assertRedirect(config('urls.dashboard.base') . '?oauth=fail');
  }

  public function test_vend_return_redirect_with_access_code_with_correct_data_create_account() {
    $status = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id]);
    $this->businessHeaders($account->business);

    $code="access_token";
    $domainPrefix = 'test-domain';
    $state = auth('business')->getToken();

    $url = "/api/business/pos/vend/oauth?code={$code}&domain_prefix={$domainPrefix}&state={$state}";
    $response = $this->get($url)->assertRedirect(config('urls.dashboard.base') . '?oauth=success');
    
    $accessTokenResponse = json_decode(TestHelpers::fakeAccessTokenResponse(), true);

    $this->assertDatabaseHas('vend_accounts', [
      'pos_account_id' => $account->id,
      'access_token' => $accessTokenResponse['access_token'],
      'domain_prefix' => $domainPrefix,
      'refresh_token' => $accessTokenResponse['refresh_token'],
      'expiry' => $accessTokenResponse['expires']
    ]);
  }

  public function test_creating_vend_account_creates_related_inventory_object() {
    $status = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id]);
    $this->businessHeaders($account->business);

    $code="access_token";
    $domainPrefix = 'test-domain';
    $state = auth('business')->getToken();

    $url = "/api/business/pos/vend/oauth?code={$code}&domain_prefix={$domainPrefix}&state={$state}";
    $this->get($url)->assertRedirect(config('urls.dashboard.base') . '?oauth=success');;
    $this->assertDatabaseHas('inventories', ['business_id' => $account->business_id]);
  }

  public function test_creating_vend_account_creates_webhook() {
    $status = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id]);
    $this->businessHeaders($account->business);

    $code="access_token";
    $domainPrefix = 'test-domain';
    $state = auth('business')->getToken();

    $url = "/api/business/pos/vend/oauth?code={$code}&domain_prefix={$domainPrefix}&state={$state}";
    $this->get($url);
    $this->assertDatabaseHas('vend_accounts', ['webhook_set' => true]);
  }

  public function test_a_customer_entering_vend_location_creates_customer_in_vend() {
    $status = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id, 'type' => 'vend']);
    $vendAccount = factory(\App\Models\Business\VendAccount::class)->create(['pos_account_id' => $account->id]);
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);

    factory(\App\Models\Business\Location::class)->create(['business_id' => $account->business_id]);
    factory(\App\Models\Location\ActiveLocation::class)->create(['location_id' => $account->business->location->id, 'customer_id' => $customer->id]);
    $this->assertDatabaseHas('active_locations', ['customer_id' => $customer->id]);
    $activeLocation = ActiveLocation::first();
    $this->assertNotNull($activeLocation->bill_identifier);
  }

  public function test_a_customer_leaving_vend_location_with_no_or_paid_transaction_deletes_customer() {
     $status = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id, 'type' => 'vend']);
    $vendAccount = factory(\App\Models\Business\VendAccount::class)->create(['pos_account_id' => $account->id]);
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);

    factory(\App\Models\Business\Location::class)->create(['business_id' => $account->business_id]);

    $activeLocation = factory(\App\Models\Location\ActiveLocation::class)->create(['location_id' => $account->business->location->id, 'customer_id' => $customer->id]);
    $this->assertNotNull($activeLocation->bill_identifier);
    $this->assertEquals(1, ActiveLocation::count());

    $activeLocation->delete();

    $this->assertEquals(0, ActiveLocation::count());
  }
}
