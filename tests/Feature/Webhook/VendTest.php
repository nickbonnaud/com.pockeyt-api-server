<?php

namespace Tests\Feature\Webhook;

use Tests\TestCase;
use App\Models\Business\ActiveItem;
use App\Models\Transaction\Transaction;
use App\Models\Refund\Refund;
use App\Models\Transaction\PurchasedItem;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use App\Helpers\VendTestHelpers as TestHelpers;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Illuminate\Support\Str;

class VendTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_a_vend_account_cannot_receive_a_webhook_without_hash_signature() {
    $vendAccount = factory(\App\Models\Business\VendAccount::class)->create();

    $body = TestHelpers::fakeReceiveWebhookNoCustomer();
    $url = "/api/webhook/vend";
    $headers = [
      'Content-Type' => 'application/x-www-form-urlencoded'
    ];

    $this->withHeaders($headers)->json('POST', $url, $body)->assertStatus(403);
  }

  public function test_a_vend_account_cannot_receive_a_webhook_with_wrong_signature() {
    $vendAccount = factory(\App\Models\Business\VendAccount::class)->create();

    $body = TestHelpers::fakeReceiveWebhookNoCustomer();
    $url = "/api/webhook/vend";

    $signature = hash_hmac('sha256', json_encode($body), env('VEND_SECRET'));

    $headers = [
      'Content-Type' => 'application/x-www-form-urlencoded',
      'X-Signature' => "signature={$signature}1234,algorithm=HMAC-SHA256"
    ];

    $this->withHeaders($headers)->json('POST', $url, $body)->assertStatus(403);
  }

  public function test_a_vend_account_webhook_with_correct_signature_returns_200_response() {
    $body = TestHelpers::fakeReceiveWebhookNoCustomer();
    $vendAccount = factory(\App\Models\Business\VendAccount::class)->create(['domain_prefix' => $body['domain_prefix']]);

    $body = TestHelpers::fakeReceiveWebhookNoCustomer();
    $url = "/api/webhook/vend";

    $signature = hash_hmac('sha256', json_encode($body), env('VEND_SECRET'));

    $headers = [
      'Content-Type' => 'application/x-www-form-urlencoded',
      'X-Signature' => "signature={$signature},algorithm=HMAC-SHA256"
    ];

    $this->withHeaders($headers)->json('POST', $url, $body)->assertStatus(200);
  }

  public function test_a_vend_account_without_correct_customer_does_not_create_transaction() {
    $body = TestHelpers::fakeReceiveWebhookNoCustomer();
    $vendAccount = factory(\App\Models\Business\VendAccount::class)->create(['domain_prefix' => $body['domain_prefix']]);
    $url = "/api/webhook/vend";

    $signature = hash_hmac('sha256', json_encode($body), env('VEND_SECRET'));

    $headers = $this->setHeaders();
    $headers['X-Signature'] = "signature={$signature},algorithm=HMAC-SHA256";
    
    $response = $this->withHeaders($headers)->json('POST', $url, $body)->assertStatus(200);
    $this->assertEquals(0, Transaction::count());
  }

  public function test_a_vend_account_with_correct_customer_creates_transaction() {
    Notification::fake();
    factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'closed']);
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $body = TestHelpers::fakeReceiveWebhookCustomer($customer->identifier);
    $vendAccount = factory(\App\Models\Business\VendAccount::class)->create(['domain_prefix' => $body['domain_prefix']]);
    $url = "/api/webhook/vend";

    $signature = hash_hmac('sha256', json_encode($body), env('VEND_SECRET'));

    $headers = $this->setHeaders();
    $headers['X-Signature'] = "signature={$signature},algorithm=HMAC-SHA256";
    
    $response = $this->withHeaders($headers)->json('POST', $url, $body)->getData();
    $this->assertEquals(1, Transaction::count());
    $jsonBody = json_decode($body['payload']);
    $this->assertDatabaseHas('transactions', [
      'customer_id' => $customer->id,
      'pos_transaction_id' => $jsonBody->id,
      'business_id' => $vendAccount->posAccount->business_id,
      'tax' => $jsonBody->totals->total_tax * 100,
      'net_sales' => $jsonBody->totals->total_price * 100,
      'total' => $jsonBody->totals->total_payment * 100
    ]);
  }

  public function test_a_vend_account_webhook_creates_inventory_item_if_not_stored() {
    Notification::fake();
    factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'closed']);
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $body = TestHelpers::fakeReceiveWebhookCustomer($customer->identifier);
    $vendAccount = factory(\App\Models\Business\VendAccount::class)->create(['domain_prefix' => $body['domain_prefix']]);
    $url = "/api/webhook/vend";

    $signature = hash_hmac('sha256', json_encode($body), env('VEND_SECRET'));

    $headers = $this->setHeaders();
    $headers['X-Signature'] = "signature={$signature},algorithm=HMAC-SHA256";
    
    $this->assertEquals(0, ActiveItem::count());
    $response = $this->withHeaders($headers)->json('POST', $url, $body)->getData();
    $body = json_decode($body['payload']);
    $this->assertEquals(count($body->register_sale_products), ActiveItem::count());
    $this->assertDatabaseHas('active_items', [
      'main_id' => $body->register_sale_products[0]->product_id
    ]);
  }

  public function test_a_vend_account_webhook_does_not_create_inventory_item_if_already_stored() {
    Notification::fake();
    factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'closed']);
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $body = TestHelpers::fakeReceiveWebhookCustomer($customer->identifier);
    $vendAccount = factory(\App\Models\Business\VendAccount::class)->create(['domain_prefix' => $body['domain_prefix']]);
    $url = "/api/webhook/vend";

    $signature = hash_hmac('sha256', json_encode($body), env('VEND_SECRET'));

    $headers = $this->setHeaders();
    $headers['X-Signature'] = "signature={$signature},algorithm=HMAC-SHA256";

    $bodyDecoded = json_decode($body['payload']);

    factory(\App\Models\Business\ActiveItem::class)->create([
      'inventory_id' => $vendAccount->posAccount->business->inventory->id,
      'main_id' => $bodyDecoded->register_sale_products[0]->product_id,
      'price' => $bodyDecoded->register_sale_products[0]->price * 100
    ]);

    factory(\App\Models\Business\ActiveItem::class)->create([
      'inventory_id' => $vendAccount->posAccount->business->inventory->id,
      'main_id' => $bodyDecoded->register_sale_products[1]->product_id,
      'price' => $bodyDecoded->register_sale_products[1]->price * 100
    ]);

    factory(\App\Models\Business\ActiveItem::class)->create([
      'inventory_id' => $vendAccount->posAccount->business->inventory->id,
      'main_id' => $bodyDecoded->register_sale_products[2]->product_id,
      'price' => $bodyDecoded->register_sale_products[2]->price * 100
    ]);
    
    $this->assertEquals(count($bodyDecoded->register_sale_products), ActiveItem::count());
    $response = $this->withHeaders($headers)->json('POST', $url, $body)->getData();
    $this->assertEquals(count($bodyDecoded->register_sale_products), ActiveItem::count());
  }

  public function test_a_vend_account_webhook_creates_inventory_items_not_stored() {
    Notification::fake();
    factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'closed']);
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $body = TestHelpers::fakeReceiveWebhookCustomer($customer->identifier);
    $vendAccount = factory(\App\Models\Business\VendAccount::class)->create(['domain_prefix' => $body['domain_prefix']]);
    $url = "/api/webhook/vend";

    $signature = hash_hmac('sha256', json_encode($body), env('VEND_SECRET'));

    $headers = $this->setHeaders();
    $headers['X-Signature'] = "signature={$signature},algorithm=HMAC-SHA256";

    $bodyDecoded = json_decode($body['payload']);

    factory(\App\Models\Business\ActiveItem::class)->create([
      'inventory_id' => $vendAccount->posAccount->business->inventory->id,
      'main_id' => $bodyDecoded->register_sale_products[0]->product_id,
      'price' => $bodyDecoded->register_sale_products[0]->price * 100
    ]);

    factory(\App\Models\Business\ActiveItem::class)->create([
      'inventory_id' => $vendAccount->posAccount->business->inventory->id,
      'main_id' => $bodyDecoded->register_sale_products[2]->product_id,
      'price' => $bodyDecoded->register_sale_products[2]->price * 100
    ]);
    
    $this->assertEquals(count($bodyDecoded->register_sale_products) - 1, ActiveItem::count());
    $this->assertDatabaseMissing('active_items', ['main_id' => $bodyDecoded->register_sale_products[1]->product_id]);
    $response = $this->withHeaders($headers)->json('POST', $url, $body)->getData();
    $this->assertEquals(count($bodyDecoded->register_sale_products), ActiveItem::count());
    $this->assertDatabaseHas('active_items', ['main_id' => $bodyDecoded->register_sale_products[1]->product_id]);
  }

  public function test_a_vend_account_webhook_stores_quantity_of_purchased_items() {
    Notification::fake();
    factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'closed']);
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $body = TestHelpers::fakeReceiveWebhookCustomer($customer->identifier);
    $vendAccount = factory(\App\Models\Business\VendAccount::class)->create(['domain_prefix' => $body['domain_prefix']]);
    $url = "/api/webhook/vend";

    $signature = hash_hmac('sha256', json_encode($body), env('VEND_SECRET'));

    $headers = $this->setHeaders();
    $headers['X-Signature'] = "signature={$signature},algorithm=HMAC-SHA256";
    
    $response = $this->withHeaders($headers)->json('POST', $url, $body)->getData();
  
    $body = json_decode($body['payload']);
    $totalPurchasedItems = 0;
    foreach ($body->register_sale_products as $product) {
      $totalPurchasedItems = $totalPurchasedItems + $product->quantity;
    }
    $this->assertEquals($totalPurchasedItems, (Transaction::first())->purchasedItems->count());
  }

  public function test_a_vend_refund_webhook_creates_refund_if_status_is_paid_full() {
    factory(\App\Models\Refund\RefundStatus::class)->create(['name' => 'refund_pending']);
    $status = factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'paid']);
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $body = TestHelpers::fakeFullReturnWebhook($customer->identifier);
    $vendAccount = factory(\App\Models\Business\VendAccount::class)->create(['domain_prefix' => $body['domain_prefix']]);

    $url = "/api/webhook/vend";

    $signature = hash_hmac('sha256', json_encode($body), env('VEND_SECRET'));
    $headers = $this->setHeaders();
    $headers['X-Signature'] = "signature={$signature},algorithm=HMAC-SHA256";

    $bodyDecoded = json_decode($body['payload']);

    $transaction = factory(\App\Models\Transaction\Transaction::class)->create([
      'customer_id' => $customer->id,
      'business_id' => $vendAccount->posAccount->business_id,
      'status_id' => $status->id,
      'pos_transaction_id' => $bodyDecoded->return_for,
    ]);
    
    $response = $this->withHeaders($headers)->json('POST', $url, $body)->getData();
    $this->assertDatabaseHas('refunds', [
      'transaction_id' => $transaction->id,
      'total' => abs($bodyDecoded->totals->total_payment) * 100,
      'pos_refund_id' => $bodyDecoded->id
    ]);
  }

  public function test_a_vend_refund_webhook_creates_refund_if_status_is_paid_partial() {
    factory(\App\Models\Refund\RefundStatus::class)->create(['name' => 'refund_pending']);
    $status = factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'paid']);
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $body = TestHelpers::fakePartialReturnWebhook($customer->identifier);
    $vendAccount = factory(\App\Models\Business\VendAccount::class)->create(['domain_prefix' => $body['domain_prefix']]);

    $url = "/api/webhook/vend";

    $signature = hash_hmac('sha256', json_encode($body), env('VEND_SECRET'));
    $headers = $this->setHeaders();
    $headers['X-Signature'] = "signature={$signature},algorithm=HMAC-SHA256";

    $bodyDecoded = json_decode($body['payload']);

    $transaction = factory(\App\Models\Transaction\Transaction::class)->create([
      'customer_id' => $customer->id,
      'business_id' => $vendAccount->posAccount->business_id,
      'status_id' => $status->id,
      'pos_transaction_id' => $bodyDecoded->return_for,
    ]);
    
    $response = $this->withHeaders($headers)->json('POST', $url, $body)->getData();
    $this->assertDatabaseHas('refunds', [
      'transaction_id' => $transaction->id,
      'total' => abs($bodyDecoded->totals->total_payment) * 100,
      'pos_refund_id' => $bodyDecoded->id
    ]);
  }

  public function test_a_vend_account_refund_webhook_adjusts_transaction_if_status_not_paid_partial() {
    Notification::fake();
    factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'closed']);
    factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'paid']);

    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $body = TestHelpers::fakeReceiveWebhookCustomer($customer->identifier);
    $vendAccount = factory(\App\Models\Business\VendAccount::class)->create(['domain_prefix' => $body['domain_prefix']]);

    $url = "/api/webhook/vend";

    $signature = hash_hmac('sha256', json_encode($body), env('VEND_SECRET'));
    $headers = $this->setHeaders();
    $headers['X-Signature'] = "signature={$signature},algorithm=HMAC-SHA256";
    
    $response = $this->withHeaders($headers)->json('POST', $url, $body)->getData();

    $bodyDecoded = json_decode($body['payload']);
    $this->assertDatabaseHas('transactions', [
      'customer_id' => $customer->id,
      'pos_transaction_id' => $bodyDecoded->id,
      'business_id' => $vendAccount->posAccount->business_id,
      'tax' => $bodyDecoded->totals->total_tax * 100,
      'net_sales' => $bodyDecoded->totals->total_price * 100,
      'total' => $bodyDecoded->totals->total_payment * 100
    ]);

    $totalPurchasedItems = 0;
    foreach ($bodyDecoded->register_sale_products as $product) {
      $totalPurchasedItems = $totalPurchasedItems + $product->quantity;
    }
    $this->assertEquals($totalPurchasedItems, (Transaction::first())->purchasedItems->count());

    $bodyRefund = TestHelpers::fakePartialReturnWebhook($customer->identifier);
    $signature = hash_hmac('sha256', json_encode($bodyRefund), env('VEND_SECRET'));
    $headers = $this->setHeaders();
    $headers['X-Signature'] = "signature={$signature},algorithm=HMAC-SHA256";

    $bodyDecodedRefund = json_decode($bodyRefund['payload']);
    $response = $this->withHeaders($headers)->json('POST', $url, $bodyRefund)->getData();

    $this->assertDatabaseHas('transactions', [
      'customer_id' => $customer->id,
      'pos_transaction_id' => $bodyDecoded->id,
      'business_id' => $vendAccount->posAccount->business_id,
      'tax' => ($bodyDecoded->totals->total_tax + $bodyDecodedRefund->totals->total_tax) * 100,
      'net_sales' => ($bodyDecoded->totals->total_price + $bodyDecodedRefund->totals->total_price) * 100,
      'total' => ($bodyDecoded->totals->total_payment + $bodyDecodedRefund->totals->total_payment) * 100
    ]);

    $totalRefundedItems = 0;
    foreach ($bodyDecodedRefund->register_sale_products as $product) {
      $totalRefundedItems = $totalRefundedItems + abs($product->quantity);
    }

    $this->assertEquals($totalPurchasedItems - $totalRefundedItems, (Transaction::first())->purchasedItems->count());
  }

  public function test_a_vend_account_refund_webhook_deletes_transaction_if_status_not_paid_full() {
    Notification::fake();
    factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'closed']);
    factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'paid']);

    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $body = TestHelpers::fakeFullReturnWebhookInitial($customer->identifier);
    $vendAccount = factory(\App\Models\Business\VendAccount::class)->create(['domain_prefix' => $body['domain_prefix']]);

    $url = "/api/webhook/vend";

    $signature = hash_hmac('sha256', json_encode($body), env('VEND_SECRET'));
    $headers = $this->setHeaders();
    $headers['X-Signature'] = "signature={$signature},algorithm=HMAC-SHA256";
    
    $response = $this->withHeaders($headers)->json('POST', $url, $body)->getData();

    $bodyDecoded = json_decode($body['payload']);
    $this->assertDatabaseHas('transactions', [
      'customer_id' => $customer->id,
      'pos_transaction_id' => $bodyDecoded->id,
      'business_id' => $vendAccount->posAccount->business_id,
      'tax' => $bodyDecoded->totals->total_tax * 100,
      'net_sales' => $bodyDecoded->totals->total_price * 100,
      'total' => $bodyDecoded->totals->total_payment * 100
    ]);

    $totalPurchasedItems = 0;
    foreach ($bodyDecoded->register_sale_products as $product) {
      $totalPurchasedItems = $totalPurchasedItems + $product->quantity;
    }
    $this->assertEquals($totalPurchasedItems, (Transaction::first())->purchasedItems->count());

    $bodyRefund = TestHelpers::fakeFullReturnWebhook($customer->identifier);
    $signature = hash_hmac('sha256', json_encode($bodyRefund), env('VEND_SECRET'));
    $headers = $this->setHeaders();
    $headers['X-Signature'] = "signature={$signature},algorithm=HMAC-SHA256";

    $bodyDecodedRefund = json_decode($bodyRefund['payload']);
    $response = $this->withHeaders($headers)->json('POST', $url, $bodyRefund)->getData();

    $this->assertEquals(0, Transaction::count());
    $this->assertEquals(0, PurchasedItem::count());
  }

  public function test_a_vend_account_does_not_double_refund() {
    factory(\App\Models\Refund\RefundStatus::class)->create(['name' => 'refund_pending']);
    $status = factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'paid']);
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $bodyFirst = TestHelpers::fakeDoubleReturnFirst($customer->identifier);
    $vendAccount = factory(\App\Models\Business\VendAccount::class)->create(['domain_prefix' => $bodyFirst['domain_prefix']]);

    $url = "/api/webhook/vend";

    $signature = hash_hmac('sha256', json_encode($bodyFirst), env('VEND_SECRET'));
    $headers = $this->setHeaders();
    $headers['X-Signature'] = "signature={$signature},algorithm=HMAC-SHA256";

    $bodyDecoded = json_decode($bodyFirst['payload']);

    $transaction = factory(\App\Models\Transaction\Transaction::class)->create([
      'customer_id' => $customer->id,
      'business_id' => $vendAccount->posAccount->business_id,
      'status_id' => $status->id,
      'pos_transaction_id' => $bodyDecoded->return_for,
    ]);
    
    $response = $this->withHeaders($headers)->json('POST', $url, $bodyFirst)->getData();
    $this->assertEquals(1, Refund::count());

    $bodySecond = TestHelpers::fakeDoubleReturnSecond($customer->identifier);
    $signature = hash_hmac('sha256', json_encode($bodySecond), env('VEND_SECRET'));
    $headers = $this->setHeaders();
    $headers['X-Signature'] = "signature={$signature},algorithm=HMAC-SHA256";
    $response = $this->withHeaders($headers)->json('POST', $url, $bodySecond)->getData();
    $this->assertEquals(1, Refund::count());
  }

  public function test_a_vend_webhook_transaction_with_employee_creates_employee_if_employee_not_stored() {
    Notification::fake();
    factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'closed']);
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $body = TestHelpers::fakeReceiveWebhookCustomer($customer->identifier);
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'vend']);
    $vendAccount = factory(\App\Models\Business\VendAccount::class)->create(['domain_prefix' => $body['domain_prefix'], 'pos_account_id' => $posAccount->id]);
    $url = "/api/webhook/vend";

    $signature = hash_hmac('sha256', json_encode($body), env('VEND_SECRET'));

    $headers = $this->setHeaders();
    $headers['X-Signature'] = "signature={$signature},algorithm=HMAC-SHA256";
    
    $externalEmployeeId = json_decode($body['payload'])->user_id;
    $this->assertDatabaseMissing('employees', ['external_id' => $externalEmployeeId]);
    $response = $this->withHeaders($headers)->json('POST', $url, $body)->getData();
    $transaction = \App\Models\Transaction\Transaction::first();
    $status = \App\Models\Transaction\TransactionStatus::where(['name' => 'paid'])->first();
    $transaction->update(['status_id' => $status->id]);
    $this->assertDatabaseHas('employees', ['external_id' => $externalEmployeeId]);
  }

  public function test_a_vend_webhook_transaction_with_employee_does_not_create_employee_if_already_stored() {
    Notification::fake();
    factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'closed']);
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $body = TestHelpers::fakeReceiveWebhookCustomer($customer->identifier);
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'vend']);
    $vendAccount = factory(\App\Models\Business\VendAccount::class)->create(['domain_prefix' => $body['domain_prefix'], 'pos_account_id' => $posAccount->id]);
    $url = "/api/webhook/vend";

    $signature = hash_hmac('sha256', json_encode($body), env('VEND_SECRET'));

    $headers = $this->setHeaders();
    $headers['X-Signature'] = "signature={$signature},algorithm=HMAC-SHA256";
    
    $externalEmployeeId = json_decode($body['payload'])->user_id;
    factory(\App\Models\Business\Employee::class)->create(['external_id' => $externalEmployeeId, 'business_id' => $posAccount->business_id]);
    $this->assertEquals(1, \App\Models\Business\Employee::count());
    $response = $this->withHeaders($headers)->json('POST', $url, $body)->getData();
    $transaction = \App\Models\Transaction\Transaction::first();
    $status = \App\Models\Transaction\TransactionStatus::where(['name' => 'paid'])->first();
    $transaction->update(['status_id' => $status->id]);
    $this->assertEquals(1, \App\Models\Business\Employee::count());
  }

  



  private function setHeaders() {
    return [
      'accept-encoding' => 'gzip',
      'x-vend-webhook-source' => 'hook',
      'x-vend-webhook-id' => '0af7b240-abf0-11e9-fb5b-ae437a567623',
      'content-type' => 'application/x-www-form-urlencoded',
      'content-length' => '3639',
      'connection' => 'close',
      'user-agent' => 'Vend/2.0',
      'host' => 'pockeyt-test.com',
    ];
  }
}
