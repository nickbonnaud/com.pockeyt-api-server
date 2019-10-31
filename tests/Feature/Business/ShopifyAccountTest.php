<?php

namespace Tests\Feature\Business;

use JWTAuth;
use Tests\TestCase;
use App\Models\Business\ShopifyAccount;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use App\Handlers\Http\HttpHandler;

class ShopifyAccountTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function test_shopify_oauth_must_have_hmac_signature() {
    $status = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id]);

    $this->json('GET', "/api/business/pos/shopify/oauth")->assertRedirect(config('urls.dashboard.base') . '?oauth=fail');
  }

  public function test_shopify_oauth_redirect_must_have_valid_hmac_signature() {
    $status = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id]);
    $timestamp = time();

    $query = "shop=some-shop.myshopify.com&timestamp={$timestamp}";
    $hmac = hash_hmac('sha256', ($query . "&invalid=not_valid"), env('SHOPIFY_SECRET'));
    $query = "{$query}&hmac={$hmac}";

    $this->json('GET', "/api/business/pos/shopify/oauth?{$query}")->assertRedirect(config('urls.dashboard.base') . '?oauth=fail');
  }

  public function test_shopify_oauth_redirects_to_login_if_not_logged_in() {
    $status = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id]);
    $timestamp = time();

    $query = "shop=some-shop.myshopify.com&timestamp={$timestamp}";
    $hmac = hash_hmac('sha256', ($query), env('SHOPIFY_SECRET'));
    $query = "hmac={$hmac}&{$query}";
    $url = "/api/business/pos/shopify/oauth?{$query}";
    $this->json('GET', $url)->assertRedirect(config('urls.dashboard.base') . '/auth/login');
  }

  public function test_shopify_oauth_redirects_to_correct_url_with_correct_data() {
    $status = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id]);
    $timestamp = time();

    $query = "shop=some-shop.myshopify.com&timestamp={$timestamp}";
    $hmac = hash_hmac('sha256', ($query), env('SHOPIFY_SECRET'));
    $query = "hmac={$hmac}&{$query}";
    $headers = $this->businessHeaders($account->business);
    $url = "/api/business/pos/shopify/oauth?{$query}";
    $response = $this->json('GET', $url);

    $apiKey = env('SHOPIFY_CLIENT_ID');
    $scopes = "read_products,read_orders,read_draft_orders";
    $redirectUrl = url("/api/business/pos/shopify/oauth");
    $state = auth('business')->getToken();

    $redirectUrl = "https://some-shop.myshopify.com/admin/oauth/authorize?client_id={$apiKey}&scope={$scopes}&redirect_uri={$redirectUrl}&state={$state}";
    $response->assertRedirect($redirectUrl);
  }

  public function test_shopify_redirect_with_access_code_must_have_hmac() {
    $status = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id]);
    $code="access_token";

    $response = $this->json('GET', "/api/business/pos/shopify/oauth?code={$code}")->assertRedirect(config('urls.dashboard.base') . '?oauth=fail');
  }

  public function test_shopify_redirect_with_access_code_must_have_correct_hmac() {
    $status = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id]);
    $this->businessHeaders($account->business);

    $code="access_token";
    $timestamp = time();
    $state = auth('business')->getToken();
    $shop = "some-shop.myshopify.com";

    $query = "code={$code}&shop={$shop}&state={$state}&timestamp={$timestamp}";
    $hmac = hash_hmac('sha256', ($query), env('SHOPIFY_SECRET'));
    $query = "hmac={$hmac}1235A&{$query}";

    $url = "/api/business/pos/shopify/oauth?{$query}";
    $this->json('GET', $url)->assertRedirect(config('urls.dashboard.base') . '?oauth=fail');
  }

  public function test_shopify_redirect_with_access_code_must_have_state() {
    $status = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id]);

    $code="access_token";
    $timestamp = time();
    $shop = "some-shop.myshopify.com";

    $query = "code={$code}&shop={$shop}&timestamp={$timestamp}";
    $hmac = hash_hmac('sha256', ($query), env('SHOPIFY_SECRET'));
    $query = "hmac={$hmac}&{$query}";

    $url = "/api/business/pos/shopify/oauth?{$query}";
    $this->json('GET', $url)->assertRedirect(config('urls.dashboard.base') . '?oauth=fail');
  }

  public function test_shopify_redirect_with_access_code_must_have_valid_state() {
    $status = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id]);
    $this->businessHeaders($account->business);

    $code="access_token";
    $timestamp = time();
    $state = auth('business')->getToken();
    $shop = "some-shop.myshopify.com";

    $query = "code={$code}&shop={$shop}&state={$state}2345gg&timestamp={$timestamp}";
    $hmac = hash_hmac('sha256', ($query), env('SHOPIFY_SECRET'));
    $query = "hmac={$hmac}&{$query}";

    $url = "/api/business/pos/shopify/oauth?{$query}";
    $this->json('GET', $url)->assertRedirect(config('urls.dashboard.base') . '?oauth=fail');
  }

  public function test_shopify_redirect_with_access_token_must_have_shop() {
    $status = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id]);
    $this->businessHeaders($account->business);

    $code="access_token";
    $timestamp = time();
    $state = auth('business')->getToken();
    $shop = "some-shop.myshopify.com";

    $query = "code={$code}&state={$state}&timestamp={$timestamp}";
    $hmac = hash_hmac('sha256', ($query), env('SHOPIFY_SECRET'));
    $query = "hmac={$hmac}&{$query}";

    $url = "/api/business/pos/shopify/oauth?{$query}";
    $this->json('GET', $url)->assertRedirect(config('urls.dashboard.base') . '?oauth=fail');
  }

  public function test_shopify_redirect_with_access_token_must_have_valid_shop() {
    $status = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id]);
    $this->businessHeaders($account->business);

    $code="access_token";
    $timestamp = time();
    $state = auth('business')->getToken();
    $shop = "some-shop.shopify.com";

    $query = "code={$code}&shop={$shop}&state={$state}&timestamp={$timestamp}";
    $hmac = hash_hmac('sha256', ($query), env('SHOPIFY_SECRET'));
    $query = "hmac={$hmac}&{$query}";

    $url = "/api/business/pos/shopify/oauth?{$query}";
    $this->json('GET', $url)->assertRedirect(config('urls.dashboard.base') . '?oauth=fail');
  }

  public function test_shopify_redirect_with_access_token_correct_data_creates_account() {
    $status = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id]);
    $this->businessHeaders($account->business);

    $code = 'temp_token';
    $timestamp = time();
    $state = auth('business')->getToken();
    $shop = "some-shop.myshopify.com";

    $query = "code={$code}&shop={$shop}&state={$state}&timestamp={$timestamp}";
    $hmac = hash_hmac('sha256', ($query), env('SHOPIFY_SECRET'));
    $query = "hmac={$hmac}&{$query}";

    $url = "/api/business/pos/shopify/oauth?{$query}";
    $this->json('GET', $url)->assertRedirect(config('urls.dashboard.base') . '?oauth=success');
    $this->assertDatabaseHas('shopify_accounts', ['pos_account_id' => $account->id]);
  }

  public function test_creating_shopify_account_creates_a_related_inventory_object_shopify() {
    $status = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id]);
    $this->businessHeaders($account->business);

    $code = 'temp_token';
    $timestamp = time();
    $state = auth('business')->getToken();
    $shop = "some-shop.myshopify.com";

    $query = "code={$code}&shop={$shop}&state={$state}&timestamp={$timestamp}";
    $hmac = hash_hmac('sha256', ($query), env('SHOPIFY_SECRET'));
    $query = "hmac={$hmac}&{$query}";

    $url = "/api/business/pos/shopify/oauth?{$query}";
    $this->json('GET', $url)->assertRedirect(config('urls.dashboard.base') . '?oauth=success');
    $this->assertDatabaseHas('inventories', ['business_id' => $account->business_id]);
  }

  public function test_creating_a_shopify_account_creates_webhooks() {
    $status = factory(\App\Models\Business\PosAccountStatus::class)->create();
    $account = factory(\App\Models\Business\PosAccount::class)->create(['pos_account_status_id' => $status->id]);
    $this->businessHeaders($account->business);

    $code = 'temp_token';
    $timestamp = time();
    $state = auth('business')->getToken();
    $shop = "some-shop.myshopify.com";

    $query = "code={$code}&shop={$shop}&state={$state}&timestamp={$timestamp}";
    $hmac = hash_hmac('sha256', ($query), env('SHOPIFY_SECRET'));
    $query = "hmac={$hmac}&{$query}";

    $url = "/api/business/pos/shopify/oauth?{$query}";
    $this->json('GET', $url)->assertRedirect(config('urls.dashboard.base') . '?oauth=success');
    $this->assertDatabaseHas('shopify_accounts', ['webhook_set' => true]);
  }
}
