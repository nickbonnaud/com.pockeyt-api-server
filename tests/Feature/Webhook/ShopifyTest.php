<?php

namespace Tests\Feature\Webhook;

use Tests\TestCase;
use App\Helpers\ShopifyTestHelpers;
use App\Models\Transaction\Transaction;
use App\Models\Transaction\PurchasedItem;
use App\Models\Business\ActiveItem;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShopifyTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }
  
  public function test_a_shopify_account_cannot_receive_a_webhook_without_hmac_header() {
    $shopifyAccount = factory(\App\Models\Business\ShopifyAccount::class)->create();

    $body = ShopifyTestHelpers::fakeReceiveWebhook();
    $url = "/api/webhook/shopify";
    $headers = [
      "x-shopify-topic" => "orders/paid",
      "x-shopify-shop-domain" => $shopifyAccount->shop_id,
      "x-shopify-api-version" => "2019-04"
    ];

    $response = $this->json('POST', $url, $body)->assertStatus(403);
    $response = $this->json('POST', $url, $body, $headers)->assertStatus(403);
  }

  public function test_a_shopify_account_must_have_correct_hmac_header() {
    $shopifyAccount = factory(\App\Models\Business\ShopifyAccount::class)->create();

    $url = "/api/webhook/shopify";
    $body = ShopifyTestHelpers::fakeReceiveWebhook();

    $hmac = base64_encode(hash_hmac('sha256', json_encode($body), env('SHOPIFY_SECRET'), true));
    $headers = [
      "x-shopify-topic" => "orders/paid",
      "x-shopify-shop-domain" => $shopifyAccount->shop_id,
      "x-shopify-api-version" => "2019-04",
      "x-shopify-hmac-sha256" => "wrong{$hmac}"
    ];

    $response = $this->json('POST', $url, $body, $headers)->assertStatus(403);
  }

  public function test_a_shopify_account_with_correct_header_returns_200_response() {
    $shopifyAccount = factory(\App\Models\Business\ShopifyAccount::class)->create();

    $url = "/api/webhook/shopify";
    $body = ShopifyTestHelpers::fakeReceiveWebhook();

    $hmac = base64_encode(hash_hmac('sha256', json_encode($body), env('SHOPIFY_SECRET'), true));
    $headers = [
      "x-shopify-topic" => "orders/paid",
      "x-shopify-shop-domain" => $shopifyAccount->shop_id,
      "x-shopify-api-version" => "2019-04",
      "x-shopify-hmac-sha256" => $hmac
    ];

    $response = $this->json('POST', $url, $body, $headers)->assertStatus(200);
  }

  public function test_a_shopify_account_webhook_with_no_note_attributes_does_not_create_a_transaction() {
    $shopifyAccount = factory(\App\Models\Business\ShopifyAccount::class)->create();

    $url = "/api/webhook/shopify";
    $body = ShopifyTestHelpers::fakeReceiveWebhookNoNote();

    $hmac = base64_encode(hash_hmac('sha256', json_encode($body), env('SHOPIFY_SECRET'), true));
    $headers = [
      "x-shopify-topic" => "orders/paid",
      "x-shopify-shop-domain" => $shopifyAccount->shop_id,
      "x-shopify-api-version" => "2019-04",
      "x-shopify-hmac-sha256" => $hmac
    ];

    $response = $this->json('POST', $url, $body, $headers)->getData();
    $this->assertEquals(0, Transaction::count());
  }

  public function test_a_shopify_account_webhook_with_note_attributes_creates_a_transaction() {
    Notification::fake();
    $shopifyAccount = factory(\App\Models\Business\ShopifyAccount::class)->create();

    $url = "/api/webhook/shopify";
    $body = ShopifyTestHelpers::fakeReceiveWebhook();
    $customer = factory(\App\Models\Customer\Customer::class)->create(['identifier' => $body['note_attributes'][0]['value']]);

    $hmac = base64_encode(hash_hmac('sha256', json_encode($body), env('SHOPIFY_SECRET'), true));
    $headers = [
      "x-shopify-topic" => "orders/paid",
      "x-shopify-shop-domain" => $shopifyAccount->shop_id,
      "x-shopify-api-version" => "2019-04",
      "x-shopify-hmac-sha256" => $hmac
    ];

    $response = $this->json('POST', $url, $body, $headers)->getData();
    $this->assertEquals(1, Transaction::count());
    $this->assertDatabaseHas('transactions', [
      'customer_id' => $customer->id,
      'pos_transaction_id' => $body['id'],
      'business_id' => $shopifyAccount->posAccount->business_id,
      'tax' => $body['total_tax'] * 100,
      'net_sales' => $body['subtotal_price'] * 100,
      'total' => $body['total_price'] * 100
    ]);
  }

  public function test_a_shopify_account_webhook_for_a_transaction_creates_inventory_items_if_not_stored() {
    Notification::fake();
    $shopifyAccount = factory(\App\Models\Business\ShopifyAccount::class)->create();

    $url = "/api/webhook/shopify";
    $body = ShopifyTestHelpers::fakeReceiveWebhook();
    $customer = factory(\App\Models\Customer\Customer::class)->create(['identifier' => $body['note_attributes'][0]['value']]);

    $hmac = base64_encode(hash_hmac('sha256', json_encode($body), env('SHOPIFY_SECRET'), true));
    $headers = [
      "x-shopify-topic" => "orders/paid",
      "x-shopify-shop-domain" => $shopifyAccount->shop_id,
      "x-shopify-api-version" => "2019-04",
      "x-shopify-hmac-sha256" => $hmac
    ];

     $this->assertEquals(0, ActiveItem::count());
    $response = $this->json('POST', $url, $body, $headers)->getData();
    $this->assertEquals(count($body['line_items']), ActiveItem::count());
    $this->assertDatabaseHas('active_items', [
      'main_id' => $body['line_items'][0]['product_id'],
      'sub_id' => $body['line_items'][0]['variant_id'],
      'name' => $body['line_items'][0]['title'],
      'sub_name' => $body['line_items'][0]['variant_title'],
      'price' => $body['line_items'][0]['price'] * 100
    ]);
    $this->assertDatabaseHas('active_items', [
      'main_id' => $body['line_items'][1]['product_id'],
      'sub_id' => $body['line_items'][1]['variant_id'],
      'name' => $body['line_items'][1]['title'],
      'sub_name' => $body['line_items'][1]['variant_title'],
      'price' => $body['line_items'][1]['price'] * 100
    ]);
  }

  public function test_a_shopify_account_webhook_for_a_transaction_does_not_create_inventory_items_if_already_stored() {
    Notification::fake();
    $shopifyAccount = factory(\App\Models\Business\ShopifyAccount::class)->create();

    $url = "/api/webhook/shopify";
    $body = ShopifyTestHelpers::fakeReceiveWebhook();
    $customer = factory(\App\Models\Customer\Customer::class)->create(['identifier' => $body['note_attributes'][0]['value']]);

    factory(\App\Models\Business\ActiveItem::class)->create([
      'inventory_id' => $shopifyAccount->posAccount->business->inventory->id,
      'main_id' => $body['line_items'][0]['product_id'],
      'sub_id' => $body['line_items'][0]['variant_id'],
      'name' => $body['line_items'][0]['title'],
      'sub_name' => $body['line_items'][0]['variant_title'],
      'price' => $body['line_items'][0]['price'] * 100
    ]);

    factory(\App\Models\Business\ActiveItem::class)->create([
      'inventory_id' => $shopifyAccount->posAccount->business->inventory->id,
      'main_id' => $body['line_items'][1]['product_id'],
      'sub_id' => $body['line_items'][1]['variant_id'],
      'name' => $body['line_items'][1]['title'],
      'sub_name' => $body['line_items'][1]['variant_title'],
      'price' => $body['line_items'][1]['price'] * 100
    ]);

    $hmac = base64_encode(hash_hmac('sha256', json_encode($body), env('SHOPIFY_SECRET'), true));
    $headers = [
      "x-shopify-topic" => "orders/paid",
      "x-shopify-shop-domain" => $shopifyAccount->shop_id,
      "x-shopify-api-version" => "2019-04",
      "x-shopify-hmac-sha256" => $hmac
    ];

    $this->assertEquals(count($body['line_items']), ActiveItem::count());
    $response = $this->json('POST', $url, $body, $headers)->getData();
    $this->assertEquals(count($body['line_items']), ActiveItem::count());
  }

  public function test_a_shopify_webhook_correctly_stores_quantity_of_purchased_items() {
    Notification::fake();
    $shopifyAccount = factory(\App\Models\Business\ShopifyAccount::class)->create();

    $url = "/api/webhook/shopify";
    $body = ShopifyTestHelpers::fakeReceiveWebhook();
    $customer = factory(\App\Models\Customer\Customer::class)->create(['identifier' => $body['note_attributes'][0]['value']]);

    $hmac = base64_encode(hash_hmac('sha256', json_encode($body), env('SHOPIFY_SECRET'), true));
    $headers = [
      "x-shopify-topic" => "orders/paid",
      "x-shopify-shop-domain" => $shopifyAccount->shop_id,
      "x-shopify-api-version" => "2019-04",
      "x-shopify-hmac-sha256" => $hmac
    ];

    $response = $this->json('POST', $url, $body, $headers)->getData();
    $totalPurchasedItems = $body['line_items'][0]['quantity'] + $body['line_items'][1]['quantity'];
    $this->assertEquals($totalPurchasedItems, (Transaction::first())->purchasedItems->count());
  }

  public function test_a_shopify_refund_webhook_creates_a_refund_if_status_is_paid() {
    factory(\App\Models\Refund\RefundStatus::class)->create(['name' => 'refund_pending']);
    $status = \App\Models\Transaction\TransactionStatus::where('name', 'paid')->first();
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'shopify']);
    $shopifyAccount = factory(\App\Models\Business\ShopifyAccount::class)->create(['pos_account_id' => $posAccount->id]);
    
    $orderData = json_decode(ShopifyTestHelpers::fakeCreateRefundOrder(), true);
    
    $customer = factory(\App\Models\Customer\Customer::class)->create(['identifier' => $orderData['order']['note_attributes'][0]['value']]);
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);

    $transaction = factory(\App\Models\Transaction\Transaction::class)->create([
      'customer_id' => $customer->id,
      'business_id' => $posAccount->business_id,
      'status_id' => $status->id,
      'pos_transaction_id' => $orderData['order']['id'],
    ]);

    $url = "/api/webhook/shopify";
    $body = ShopifyTestHelpers::fakeReceiveRefundWebhook();

    $hmac = base64_encode(hash_hmac('sha256', json_encode($body), env('SHOPIFY_SECRET'), true));
    $headers = [
      "x-shopify-topic" => "refunds/create",
      "x-shopify-shop-domain" => $shopifyAccount->shop_id,
      "x-shopify-api-version" => "2019-04",
      "x-shopify-hmac-sha256" => $hmac
    ];

    $response = $this->json('POST', $url, $body, $headers)->getData();
    $this->assertDatabaseHas('refunds', [
      'transaction_id' => $transaction->id,
      'total' => $body['transactions'][0]['amount'] * 100,
      'pos_refund_id' => $body['id']
    ]);
  }

  public function test_a_shopify_refund_webhook_adjusts_transaction_if_status_is_not_paid_refund_not_full() {
    Notification::fake();
    $status = \App\Models\Transaction\TransactionStatus::where('name', 'notification sent')->first();
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'shopify']);
    $shopifyAccount = factory(\App\Models\Business\ShopifyAccount::class)->create(['pos_account_id' => $posAccount->id]);

    $url = "/api/webhook/shopify";
    $bodyPayWebhook = ShopifyTestHelpers::fakeReceiveWebhook();
    $customer = factory(\App\Models\Customer\Customer::class)->create(['identifier' => $bodyPayWebhook['note_attributes'][0]['value']]);
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);


    $hmac = base64_encode(hash_hmac('sha256', json_encode($bodyPayWebhook), env('SHOPIFY_SECRET'), true));
    $headers = [
      "x-shopify-topic" => "orders/paid",
      "x-shopify-shop-domain" => $shopifyAccount->shop_id,
      "x-shopify-api-version" => "2019-04",
      "x-shopify-hmac-sha256" => $hmac
    ];

    $response = $this->json('POST', $url, $bodyPayWebhook, $headers)->getData();
  
    $url = "/api/webhook/shopify";
    $bodyRefundWebhook = ShopifyTestHelpers::fakeReceiveRefundWebhook();

    $hmac = base64_encode(hash_hmac('sha256', json_encode($bodyRefundWebhook), env('SHOPIFY_SECRET'), true));
    $headers = [
      "x-shopify-topic" => "refunds/create",
      "x-shopify-shop-domain" => $shopifyAccount->shop_id,
      "x-shopify-api-version" => "2019-04",
      "x-shopify-hmac-sha256" => $hmac
    ];

    $transaction = Transaction::first();
    $totalPurchasedInitial = $bodyPayWebhook['line_items'][0]['quantity'] + $bodyPayWebhook['line_items'][1]['quantity'];
    $this->assertEquals($totalPurchasedInitial, count($transaction->purchasedItems));
    $this->assertDatabaseHas('transactions', [
      'tax' => $bodyPayWebhook['total_tax'] * 100,
      'net_sales' => $bodyPayWebhook['subtotal_price'] * 100,
      'total' => $bodyPayWebhook['total_price'] * 100
    ]);

    $this->json('POST', $url, $bodyRefundWebhook, $headers)->getData();
    $this->assertEquals($totalPurchasedInitial - $bodyRefundWebhook['refund_line_items'][0]['quantity'] -$bodyRefundWebhook['refund_line_items'][1]['quantity'], count($transaction->fresh()->purchasedItems));

    $this->assertDatabaseHas('transactions', [
      'tax' => ($bodyPayWebhook['total_tax'] * 100) - ($bodyRefundWebhook['refund_line_items'][0]['total_tax'] * 100) - ($bodyRefundWebhook['refund_line_items'][1]['total_tax'] * 100),
      'net_sales' => ($bodyPayWebhook['subtotal_price'] * 100) - ($bodyRefundWebhook['refund_line_items'][0]['subtotal'] * 100) - ($bodyRefundWebhook['refund_line_items'][1]['subtotal'] * 100),
      'total' => ($bodyPayWebhook['total_price'] * 100) - ($bodyRefundWebhook['refund_line_items'][0]['total_tax'] * 100) - ($bodyRefundWebhook['refund_line_items'][1]['total_tax'] * 100) - ($bodyRefundWebhook['refund_line_items'][0]['subtotal'] * 100) - ($bodyRefundWebhook['refund_line_items'][1]['subtotal'] * 100)
    ]);

  }

  public function test_a_shopify_refund_webhook_deletes_transaction_if_status_is_not_paid_refund_full() {
    Notification::fake();
    $status = \App\Models\Transaction\TransactionStatus::where('name', 'notification sent')->first();
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'shopify']);
    $shopifyAccount = factory(\App\Models\Business\ShopifyAccount::class)->create(['pos_account_id' => $posAccount->id]);

    $url = "/api/webhook/shopify";
    $bodyPayWebhook = ShopifyTestHelpers::fakeReceiveWebhook();
    $customer = factory(\App\Models\Customer\Customer::class)->create(['identifier' => $bodyPayWebhook['note_attributes'][0]['value']]);
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);


    $hmac = base64_encode(hash_hmac('sha256', json_encode($bodyPayWebhook), env('SHOPIFY_SECRET'), true));
    $headers = [
      "x-shopify-topic" => "orders/paid",
      "x-shopify-shop-domain" => $shopifyAccount->shop_id,
      "x-shopify-api-version" => "2019-04",
      "x-shopify-hmac-sha256" => $hmac
    ];

    $this->json('POST', $url, $bodyPayWebhook, $headers);
    $this->assertEquals(1, Transaction::count());
    $transaction = Transaction::first();
    $totalPurchasedInitial = $bodyPayWebhook['line_items'][0]['quantity'] + $bodyPayWebhook['line_items'][1]['quantity'];
    $this->assertEquals($totalPurchasedInitial, count($transaction->purchasedItems));
  
    $url = "/api/webhook/shopify";
    $bodyRefundWebhook = ShopifyTestHelpers::fakeReceiveRefundFullWebhook();

    $hmac = base64_encode(hash_hmac('sha256', json_encode($bodyRefundWebhook), env('SHOPIFY_SECRET'), true));
    $headers = [
      "x-shopify-topic" => "refunds/create",
      "x-shopify-shop-domain" => $shopifyAccount->shop_id,
      "x-shopify-api-version" => "2019-04",
      "x-shopify-hmac-sha256" => $hmac
    ];

    $this->json('POST', $url, $bodyRefundWebhook, $headers)->getData();
    $this->assertEquals(0, Transaction::count());
    $this->assertEquals(0, PurchasedItem::count());
  }
}
