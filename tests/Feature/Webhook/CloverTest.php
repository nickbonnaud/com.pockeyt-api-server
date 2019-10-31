<?php

namespace Tests\Feature\Webhook;

use Tests\TestCase;
use App\Helpers\CloverTestHelpers;
use App\Models\Transaction\Transaction;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Transaction\UnassignedTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Transaction\UnassignedTransactionPurchasedItem;
use App\Models\Transaction\PurchasedItem;
use App\Models\Refund\Refund;

class CloverTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_setting_up_clover_webhook_logs_verification_code() {
    $url = "/api/webhook/clover";
    $attributes = ['verificationCode' => 'not_verify_code'];
    $response = $this->json('POST', $url, $attributes)->assertStatus(200);
    $this->assertEquals('authorized', $response->getData()->success);
  }

  public function test_a_clover_webhook_request_must_have_valid_signature() {
    $url = "/api/webhook/clover";
    $attributes = $this->createPayLoadToStore();
    $response = $this->json('POST', $url, $attributes)->assertStatus(403);
  }

  public function test_a_clover_webhook_creates_unassigned_transaction_if_not_paid_create_event() {
    $url = "/api/webhook/clover";
    $attributes = $this->createPayLoadToStore();
    $headers = $this->cloverWebhookHeader();
    $cloverAccount = factory(\App\Models\Business\CloverAccount::class)->create(['merchant_id' => 'XYZVJT2ZRRRSC']);
    $response = $this->json('POST', $url, $attributes, $headers)->getData();
    $this->assertEquals("Received.", $response->success);

    $this->assertDatabaseHas('unassigned_transactions', ['business_id' => $cloverAccount->posAccount->business_id, 'pos_transaction_id' => 'not_stored']);

    $testOrder = CloverTestHelpers::getNotStoredOrder('not_stored');
    $storedTransaction = UnassignedTransaction::where('pos_transaction_id', 'not_stored')->first();
    $this->assertEquals($testOrder['total'], $storedTransaction->tax + $storedTransaction->net_sales);
  }

  public function test_a_clover_webhook_unassigned_transaction_stores_unassigned_purchased_items_create_event() {
    $url = "/api/webhook/clover";
    $attributes = $this->createPayLoadToStore();
    $headers = $this->cloverWebhookHeader();
    $cloverAccount = factory(\App\Models\Business\CloverAccount::class)->create(['merchant_id' => 'XYZVJT2ZRRRSC']);
    $response = $this->json('POST', $url, $attributes, $headers)->getData();
    $this->assertEquals("Received.", $response->success);

    $storedTransaction = UnassignedTransaction::where('pos_transaction_id', 'not_stored')->first();
    $testOrder = CloverTestHelpers::getNotStoredOrder('not_stored');

    $this->assertDatabaseHas('unassigned_transaction_purchased_items', ['unassigned_transaction_id' => $storedTransaction->id]);
    $this->assertEquals(count($testOrder['lineItems']['elements']), count($storedTransaction->purchasedItems));
  }

  public function test_a_clover_webhook_update_add_items_updates_transaction_unassigned() {
    $url = "/api/webhook/clover";
    $attributes = $this->updatePayLoadAddItem();
    $headers = $this->cloverWebhookHeader();
    $cloverAccount = factory(\App\Models\Business\CloverAccount::class)->create(['merchant_id' => 'XYZVJT2ZRRRSC']);
    $this->json('POST', $url, $attributes, $headers)->getData();
    $storedTransaction = UnassignedTransaction::where('pos_transaction_id', 'add_items_initial')->first();
    $testOrderOld = CloverTestHelpers::getAddItemsInitial('add_items_initial');
    $this->assertEquals($testOrderOld['total'], $storedTransaction->total);
    $oldTotal = $storedTransaction->total;

    $attributes = $this->updatePayLoadAddItemAddition();
    $this->json('POST', $url, $attributes, $headers)->getData();
    $testOrderNew = CloverTestHelpers::getAddItemsAdditional('add_items_addition');
    $this->assertEquals($testOrderNew['total'], $storedTransaction->fresh()->total);
    $this->assertNotEquals($oldTotal, $storedTransaction->fresh()->total);
  }

  public function test_a_clover_webhook_update_add_items_updates_purchased_items_unassigned() {
    $url = "/api/webhook/clover";
    $attributes = $this->updatePayLoadAddItem();
    $headers = $this->cloverWebhookHeader();
    $cloverAccount = factory(\App\Models\Business\CloverAccount::class)->create(['merchant_id' => 'XYZVJT2ZRRRSC']);
    $this->json('POST', $url, $attributes, $headers)->getData();
    $storedTransaction = UnassignedTransaction::where('pos_transaction_id', 'add_items_initial')->first();
    $testOrderOld = CloverTestHelpers::getAddItemsInitial('add_items_initial');
    $this->assertEquals(count($testOrderOld['lineItems']['elements']), $storedTransaction->purchasedItems->count());
    $oldCount = $storedTransaction->purchasedItems->count();

    $attributes = $this->updatePayLoadAddItemAddition();
    $this->json('POST', $url, $attributes, $headers)->getData();
    $testOrderNew = CloverTestHelpers::getAddItemsAdditional('add_items_addition');
    $this->assertEquals(count($testOrderNew['lineItems']['elements']), $storedTransaction->fresh()->purchasedItems->count());
    $this->assertNotEquals($oldCount, $storedTransaction->fresh()->purchasedItems->count());
  }

  public function test_a_clover_webhook_update_remove_items_updates_transaction_unassigned() {
    $url = "/api/webhook/clover";
    $attributes = $this->updatePayLoadAddItemAddition();
    $attributes['merchants']['XYZVJT2ZRRRSC'][0]['type'] = "CREATE";
    $headers = $this->cloverWebhookHeader();
    $cloverAccount = factory(\App\Models\Business\CloverAccount::class)->create(['merchant_id' => 'XYZVJT2ZRRRSC']);
    $this->json('POST', $url, $attributes, $headers)->getData();
    $storedTransaction = UnassignedTransaction::where('pos_transaction_id', 'add_items_initial')->first();
    $testOrderOld = CloverTestHelpers::getAddItemsAdditional('add_items_addition');
    $this->assertEquals($testOrderOld['total'], $storedTransaction->total);
    $oldTotal = $storedTransaction->total;

    $attributes = $this->updatePayLoadAddItem();
    $attributes['merchants']['XYZVJT2ZRRRSC'][0]['type'] = "UPDATE";
    $this->json('POST', $url, $attributes, $headers)->getData();
    $testOrderNew = CloverTestHelpers::getAddItemsInitial('add_items_initial');
    $this->assertEquals($testOrderNew['total'], $storedTransaction->fresh()->total);
    $this->assertNotEquals($oldTotal, $storedTransaction->fresh()->total);
  }

  public function test_a_clover_webhook_update_remove_items_updates_purchased_items_unassigned() {
    $url = "/api/webhook/clover";
    $attributes = $this->updatePayLoadAddItemAddition();
    $attributes['merchants']['XYZVJT2ZRRRSC'][0]['type'] = "CREATE";
    $headers = $this->cloverWebhookHeader();
    $cloverAccount = factory(\App\Models\Business\CloverAccount::class)->create(['merchant_id' => 'XYZVJT2ZRRRSC']);
    $this->json('POST', $url, $attributes, $headers)->getData();
    $storedTransaction = UnassignedTransaction::where('pos_transaction_id', 'add_items_initial')->first();
    $testOrderOld = CloverTestHelpers::getAddItemsAdditional('add_items_addition');
    $this->assertEquals(count($testOrderOld['lineItems']['elements']), $storedTransaction->purchasedItems->count());
    $oldCount = $storedTransaction->purchasedItems->count();

    $attributes = $this->updatePayLoadAddItem();
    $attributes['merchants']['XYZVJT2ZRRRSC'][0]['type'] = "UPDATE";
    $this->json('POST', $url, $attributes, $headers)->getData();
    $testOrderNew = CloverTestHelpers::getAddItemsInitial('add_items_initial');
    $this->assertEquals(count($testOrderNew['lineItems']['elements']), $storedTransaction->fresh()->purchasedItems->count());
    $this->assertNotEquals($oldCount, $storedTransaction->fresh()->purchasedItems->count());
  }

  public function test_a_clover_webhook_update_add_items_updates_transaction_assigned() {
    $url = "/api/webhook/clover";
    $attributes = $this->updatePayLoadAddItem();
    $headers = $this->cloverWebhookHeader();
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'clover']);
    $cloverAccount = factory(\App\Models\Business\CloverAccount::class)->create(['merchant_id' => 'XYZVJT2ZRRRSC', 'pos_account_id' => $posAccount->id]);
    factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'open']);
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);

    $this->json('POST', $url, $attributes, $headers)->getData();
    $unassignedTransaction = UnassignedTransaction::where('pos_transaction_id', 'add_items_initial')->first();

    $unassignedTransaction->customer_id = $customer->id;
    $unassignedTransaction->save();

    $assignedTransaction = Transaction::where('pos_transaction_id', 'add_items_initial')->first();
    $this->assertEquals($customer->id, $assignedTransaction->customer_id);
    
    $testOrderOld = CloverTestHelpers::getAddItemsInitial('add_items_initial');
    $this->assertEquals($testOrderOld['total'], $assignedTransaction->total);
    $oldTotal = $assignedTransaction->total;

    $attributes = $this->updatePayLoadAddItemAddition();
    $this->json('POST', $url, $attributes, $headers)->getData();
    $testOrderNew = CloverTestHelpers::getAddItemsAdditional('add_items_addition');
    $this->assertEquals($testOrderNew['total'], $assignedTransaction->fresh()->total);
    $this->assertNotEquals($oldTotal, $assignedTransaction->fresh()->total);
  }

  public function test_a_clover_webhook_update_add_items_updates_purchased_items_assigned() {
    $url = "/api/webhook/clover";
    $attributes = $this->updatePayLoadAddItem();
    $headers = $this->cloverWebhookHeader();
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'clover']);
    $cloverAccount = factory(\App\Models\Business\CloverAccount::class)->create(['merchant_id' => 'XYZVJT2ZRRRSC', 'pos_account_id' => $posAccount->id]);
    factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'open']);
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);

    $this->json('POST', $url, $attributes, $headers)->getData();
    $unassignedTransaction = UnassignedTransaction::where('pos_transaction_id', 'add_items_initial')->first();
    $unassignedTransaction->customer_id = $customer->id;
    $unassignedTransaction->save();

    $assignedTransaction = Transaction::where('pos_transaction_id', 'add_items_initial')->first();
    $this->assertEquals($customer->id, $assignedTransaction->customer_id);

    $testOrderOld = CloverTestHelpers::getAddItemsInitial('add_items_initial');
    $this->assertEquals(count($testOrderOld['lineItems']['elements']), $assignedTransaction->purchasedItems->count());
    $oldCount = $assignedTransaction->purchasedItems->count();

    $attributes = $this->updatePayLoadAddItemAddition();
    $this->json('POST', $url, $attributes, $headers)->getData();
    $testOrderNew = CloverTestHelpers::getAddItemsAdditional('add_items_addition');
    $this->assertEquals(count($testOrderNew['lineItems']['elements']), $assignedTransaction->fresh()->purchasedItems->count());
    $this->assertNotEquals($oldCount, $assignedTransaction->fresh()->purchasedItems->count());
  }

  public function test_a_clover_webhook_update_remove_items_updates_transaction_assigned() {
    $url = "/api/webhook/clover";
    $attributes = $this->updatePayLoadAddItemAddition();
    $attributes['merchants']['XYZVJT2ZRRRSC'][0]['type'] = "CREATE";
    $headers = $this->cloverWebhookHeader();
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'clover']);
    $cloverAccount = factory(\App\Models\Business\CloverAccount::class)->create(['merchant_id' => 'XYZVJT2ZRRRSC', 'pos_account_id' => $posAccount->id]);

    factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'open']);
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);

    $this->json('POST', $url, $attributes, $headers)->getData();
    $unassignedTransaction = UnassignedTransaction::where('pos_transaction_id', 'add_items_initial')->first();

    $unassignedTransaction->customer_id = $customer->id;
    $unassignedTransaction->save();

    $assignedTransaction = Transaction::where('pos_transaction_id', 'add_items_initial')->first();

    $testOrderOld = CloverTestHelpers::getAddItemsAdditional('add_items_addition');
    $this->assertEquals($testOrderOld['total'], $assignedTransaction->total);
    $oldTotal = $assignedTransaction->total;

    $attributes = $this->updatePayLoadAddItem();
    $attributes['merchants']['XYZVJT2ZRRRSC'][0]['type'] = "UPDATE";
    $this->json('POST', $url, $attributes, $headers)->getData();
    $testOrderNew = CloverTestHelpers::getAddItemsInitial('add_items_initial');
    $this->assertEquals($testOrderNew['total'], $assignedTransaction->fresh()->total);
    $this->assertNotEquals($oldTotal, $assignedTransaction->fresh()->total);
  }

  public function test_a_clover_webhook_update_remove_items_updates_purchased_items_assigned() {
    $url = "/api/webhook/clover";
    $attributes = $this->updatePayLoadAddItemAddition();
    $attributes['merchants']['XYZVJT2ZRRRSC'][0]['type'] = "CREATE";
    $headers = $this->cloverWebhookHeader();

    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'clover']);
    $cloverAccount = factory(\App\Models\Business\CloverAccount::class)->create(['merchant_id' => 'XYZVJT2ZRRRSC', 'pos_account_id' => $posAccount->id]);

    factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'open']);
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);

    $this->json('POST', $url, $attributes, $headers)->getData();
    $unassignedTransaction = UnassignedTransaction::where('pos_transaction_id', 'add_items_initial')->first();
    $unassignedTransaction->customer_id = $customer->id;
    $unassignedTransaction->save();

    $assignedTransaction = Transaction::where('pos_transaction_id', 'add_items_initial')->first();


    $testOrderOld = CloverTestHelpers::getAddItemsAdditional('add_items_addition');
    $this->assertEquals(count($testOrderOld['lineItems']['elements']), $assignedTransaction->purchasedItems->count());
    $oldCount = $assignedTransaction->purchasedItems->count();

    $attributes = $this->updatePayLoadAddItem();
    $attributes['merchants']['XYZVJT2ZRRRSC'][0]['type'] = "UPDATE";
    $this->json('POST', $url, $attributes, $headers)->getData();
    $testOrderNew = CloverTestHelpers::getAddItemsInitial('add_items_initial');
    $this->assertEquals(count($testOrderNew['lineItems']['elements']), $assignedTransaction->fresh()->purchasedItems->count());
    $this->assertNotEquals($oldCount, $assignedTransaction->fresh()->purchasedItems->count());
  }





  /////////////////////////////// Unassigned ///////////////////////////////////////

  public function test_a_clover_webhook_paid_is_not_stored_create_event() {
    $url = "/api/webhook/clover";
    $attributes = $this->createPayLoadPaid();
    $headers = $this->cloverWebhookHeader();
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'clover']);
    $cloverAccount = factory(\App\Models\Business\CloverAccount::class)->create(['merchant_id' => 'XYZVJT2ZRRRSC', 'pos_account_id' => $posAccount->id]);
    $response = $this->json('POST', $url, $attributes, $headers)->getData();
    $this->assertEquals("Received.", $response->success);
    $this->assertEquals(0, UnassignedTransaction::count());
    $this->assertEquals(0, UnassignedTransactionPurchasedItem::count());
  }

  public function test_a_clover_webhook_paid_is_not_stored_update_event() {
    $url = "/api/webhook/clover";
    $attributes = $this->createPayLoadInitialNotTender();
    $headers = $this->cloverWebhookHeader();
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'clover']);
    $cloverAccount = factory(\App\Models\Business\CloverAccount::class)->create(['merchant_id' => 'XYZVJT2ZRRRSC', 'pos_account_id' => $posAccount->id]);
    $response = $this->json('POST', $url, $attributes, $headers)->getData();
    $this->assertEquals(1, UnassignedTransaction::count());
    $this->assertEquals(4, UnassignedTransactionPurchasedItem::count());

    $attributes = $this->createPayLoadFinalNotTender();
    $response = $this->json('POST', $url, $attributes, $headers)->getData();
    $this->assertEquals(0, UnassignedTransaction::count());
    $this->assertEquals(0, UnassignedTransactionPurchasedItem::count());
  }

  public function test_a_clover_webhook_partial_paid_create_stores_unassigned_transaction_modified_total() {
    $url = "/api/webhook/clover";
    $attributes = $this->createPayLoadPartial();
    $headers = $this->cloverWebhookHeader();
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'clover']);
    $cloverAccount = factory(\App\Models\Business\CloverAccount::class)->create(['merchant_id' => 'XYZVJT2ZRRRSC', 'pos_account_id' => $posAccount->id]);
    $response = $this->json('POST', $url, $attributes, $headers)->getData();
    $storedTransaction = UnassignedTransaction::where('pos_transaction_id', 'partial')->first();
    $testOrder = CloverTestHelpers::getPartialPaidOrder('partial');
    $this->assertEquals($testOrder['payments']['elements'][0]['amount'], $storedTransaction->partial_payment);
  }

  public function test_a_clover_webhook_partial_paid_update_stores_unassigned_transaction_modified_total() {
    $url = "/api/webhook/clover";
    $attributes = $this->createPayLoadInitialNotTender();
    $headers = $this->cloverWebhookHeader();
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'clover']);
    $cloverAccount = factory(\App\Models\Business\CloverAccount::class)->create(['merchant_id' => 'XYZVJT2ZRRRSC', 'pos_account_id' => $posAccount->id]);
    $response = $this->json('POST', $url, $attributes, $headers)->getData();
    $storedTransaction = UnassignedTransaction::where('pos_transaction_id', 'not_tender')->first();
    $this->assertEquals(0, $storedTransaction->partial_payment);

    $attributes = $this->updatePayLoadPartialNotTender();
    $testOrder = CloverTestHelpers::getPartialNotTender('not_tender');
    $this->json('POST', $url, $attributes, $headers)->getData();
    $this->assertEquals($testOrder['payments']['elements'][0]['amount'], $storedTransaction->fresh()->partial_payment);
  }

  public function test_a_clover_webhook_partial_is_not_stored_if_all_partials_paid_create_event() {
    $url = "/api/webhook/clover";
    $attributes = $this->createPayLoadPartialPaid();
    $headers = $this->cloverWebhookHeader();
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'clover']);
    $cloverAccount = factory(\App\Models\Business\CloverAccount::class)->create(['merchant_id' => 'XYZVJT2ZRRRSC', 'pos_account_id' => $posAccount->id]);
    $response = $this->json('POST', $url, $attributes, $headers)->getData();
    $this->assertEquals(0, UnassignedTransaction::count());
    $this->assertEquals(0, UnassignedTransactionPurchasedItem::count());
  }

  public function test_a_clover_webhook_partial_is_not_stored_if_all_partials_paid_update_event() {
    $url = "/api/webhook/clover";
    $attributes = $this->createPayLoadPartial();
    $headers = $this->cloverWebhookHeader();
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'clover']);
    $cloverAccount = factory(\App\Models\Business\CloverAccount::class)->create(['merchant_id' => 'XYZVJT2ZRRRSC', 'pos_account_id' => $posAccount->id]);
    $response = $this->json('POST', $url, $attributes, $headers)->getData();
    $testOrder = CloverTestHelpers::getPartialPaidOrder('partial');
    $storedTransaction = UnassignedTransaction::where('pos_transaction_id', 'partial')->first();
    $this->assertEquals($testOrder['payments']['elements'][0]['amount'], $storedTransaction->partial_payment);

    $attributes = $this->updatePartialComplete();
    $response = $this->json('POST', $url, $attributes, $headers)->getData();
    $this->assertEquals(0, UnassignedTransaction::count());
    $this->assertEquals(0, UnassignedTransactionPurchasedItem::count());
  }

  public function test_a_clover_webhook_update_does_not_change_stored_if_relevant_fields_not_changed() {
    $url = "/api/webhook/clover";
    $attributes = $this->createPayLoadToStore();
    $headers = $this->cloverWebhookHeader();
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'clover']);
    $cloverAccount = factory(\App\Models\Business\CloverAccount::class)->create(['merchant_id' => 'XYZVJT2ZRRRSC', 'pos_account_id' => $posAccount->id]);
    $this->json('POST', $url, $attributes, $headers)->getData();
    $attributes = $this->updateNotChange();
    $this->json('POST', $url, $attributes, $headers)->getData();

    $testOrder = CloverTestHelpers::getNotStoredOrder('not_stored');
    $storedTransaction = UnassignedTransaction::where('pos_transaction_id', 'not_stored')->first();
    $this->assertEquals($testOrder['total'], $storedTransaction->total);
    $this->assertEquals(count($testOrder['lineItems']['elements']), count($storedTransaction->purchasedItems));
  }

  public function test_a_clover_webhook_delete_removes_unassigned_transaction() {
    $url = "/api/webhook/clover";
    $attributes = $this->createPayLoadInitialNotTender();
    $headers = $this->cloverWebhookHeader();
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'clover']);
    $cloverAccount = factory(\App\Models\Business\CloverAccount::class)->create(['merchant_id' => 'XYZVJT2ZRRRSC', 'pos_account_id' => $posAccount->id]);
    $response = $this->json('POST', $url, $attributes, $headers)->getData();
    $this->assertEquals(1, UnassignedTransaction::count());
    $this->assertEquals(4, UnassignedTransactionPurchasedItem::count());

    $attributes = $this->createPayLoadDelete();
    $response = $this->json('POST', $url, $attributes, $headers)->getData();
    $this->assertEquals(0, UnassignedTransaction::count());
    $this->assertEquals(0, UnassignedTransactionPurchasedItem::count());
  }





  /////////////////////////////////// assigned ////////////////////////////////////

  public function test_a_clover_webhook_update_deletes_transaction_if_paid_full_not_tender() {
    factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'open']);
    $url = "/api/webhook/clover";
    $attributes = $this->createPayLoadInitialNotTender();
    $headers = $this->cloverWebhookHeader();
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'clover']);
    $cloverAccount = factory(\App\Models\Business\CloverAccount::class)->create(['merchant_id' => 'XYZVJT2ZRRRSC', 'tender_id' => 'fake_tender_id', 'pos_account_id' => $posAccount->id]);
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $this->json('POST', $url, $attributes, $headers);

    $unassignedTransaction = UnassignedTransaction::where('pos_transaction_id', 'not_tender')->first();
    $unassignedTransaction->customer_id = $customer->id;
    $unassignedTransaction->save();

    $testOrder = CloverTestHelpers::getInitialNotTender('not_tender');
    $this->assertEquals(1, Transaction::count());
    $this->assertEquals(count($testOrder['lineItems']['elements']), PurchasedItem::count());

    $attributes = $this->createPayLoadFinalNotTender();
    $this->json('POST', $url, $attributes, $headers);
    $this->assertEquals(0, Transaction::count());
    $this->assertEquals(0, PurchasedItem::count());
  }

  public function test_a_clover_webhook_update_partial_updates_partial_paid_amount() {
    factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'open']);
    $url = "/api/webhook/clover";
    $attributes = $this->createPayLoadInitialNotTender();
    $headers = $this->cloverWebhookHeader();
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'clover']);
    $cloverAccount = factory(\App\Models\Business\CloverAccount::class)->create(['merchant_id' => 'XYZVJT2ZRRRSC', 'tender_id' => 'fake_tender_id', 'pos_account_id' => $posAccount->id]);
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $this->json('POST', $url, $attributes, $headers);

    $unassignedTransaction = UnassignedTransaction::where('pos_transaction_id', 'not_tender')->first();
    $unassignedTransaction->customer_id = $customer->id;
    $unassignedTransaction->save();

    $transaction = Transaction::where('pos_transaction_id', 'not_tender')->first();
    $this->assertEquals(0, $transaction->partial_payment);

    $attributes = $this->updatePayLoadPartialNotTender();
    $this->json('POST', $url, $attributes, $headers);

    $testOrder = CloverTestHelpers::getPartialNotTender('not_tender');
    $this->assertEquals($testOrder['payments']['elements'][0]['amount'], $transaction->fresh()->partial_payment);
  }

  public function test_a_clover_webhook_update_partial_full_not_tender_deletes_transaction() {
    factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'open']);
    $url = "/api/webhook/clover";
    $attributes = $this->createPayLoadPartial();
    $headers = $this->cloverWebhookHeader();
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'clover']);
    $cloverAccount = factory(\App\Models\Business\CloverAccount::class)->create(['merchant_id' => 'XYZVJT2ZRRRSC', 'tender_id' => 'fake_tender_id', 'pos_account_id' => $posAccount->id]);
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $this->json('POST', $url, $attributes, $headers);

    $unassignedTransaction = UnassignedTransaction::where('pos_transaction_id', 'partial')->first();
    $unassignedTransaction->customer_id = $customer->id;
    $unassignedTransaction->save();

    $testOrder = CloverTestHelpers::getPartialPaidOrder('partial');
    $this->assertEquals(1, Transaction::count());
    $this->assertEquals(count($testOrder['lineItems']['elements']), PurchasedItem::count());

    $transaction = Transaction::where('pos_transaction_id', 'partial')->first();
    $this->assertEquals($testOrder['payments']['elements'][0]['amount'], $transaction->partial_payment);

    $attributes = $this->updatePartialComplete();
    $this->json('POST', $url, $attributes, $headers);

    $this->assertEquals(0, Transaction::count());
    $this->assertEquals(0, PurchasedItem::count());
  }

  public function test_a_clover_webhook_update_does_not_change_transaction_if_relevant_fields_not_changed() {
    factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'open']);
    $url = "/api/webhook/clover";
    $attributes = $this->createPayLoadToStore();
    $headers = $this->cloverWebhookHeader();
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'clover']);
    $cloverAccount = factory(\App\Models\Business\CloverAccount::class)->create(['merchant_id' => 'XYZVJT2ZRRRSC', 'tender_id' => 'fake_tender_id', 'pos_account_id' => $posAccount->id]);
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $this->json('POST', $url, $attributes, $headers)->getData();

    $unassignedTransaction = UnassignedTransaction::where('pos_transaction_id', 'not_stored')->first();
    $unassignedTransaction->customer_id = $customer->id;
    $unassignedTransaction->save();


    $attributes = $this->updateNotChange();
    $this->json('POST', $url, $attributes, $headers)->getData();

    $testOrder = CloverTestHelpers::getNotStoredOrder('not_stored');
    $storedTransaction = Transaction::where('pos_transaction_id', 'not_stored')->first();
    $this->assertEquals($testOrder['total'], $storedTransaction->total);
    $this->assertEquals(count($testOrder['lineItems']['elements']), count($storedTransaction->purchasedItems));
  }

  public function test_clover_update_paid_full_with_tender_does_not_delete_transaction() {
    factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'open']);
    $url = "/api/webhook/clover";
    $attributes = $this->createPayLoadInitialNotTender();
    $headers = $this->cloverWebhookHeader();
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'clover']);
    $cloverAccount = factory(\App\Models\Business\CloverAccount::class)->create(['merchant_id' => 'XYZVJT2ZRRRSC', 'tender_id' => 'fake_tender_id', 'pos_account_id' => $posAccount->id]);
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $this->json('POST', $url, $attributes, $headers);

    $unassignedTransaction = UnassignedTransaction::where('pos_transaction_id', 'not_tender')->first();
    $unassignedTransaction->customer_id = $customer->id;
    $unassignedTransaction->save();

    $testOrder = CloverTestHelpers::getInitialNotTender('not_tender');
    $this->assertEquals(1, Transaction::count());
    $this->assertEquals(count($testOrder['lineItems']['elements']), PurchasedItem::count());

    $attributes = $this->createPayLoadFinalTender();
    $this->json('POST', $url, $attributes, $headers);
    $this->assertEquals(1, Transaction::count());
    $this->assertEquals(count($testOrder['lineItems']['elements']), PurchasedItem::count());
    $this->assertEquals($testOrder['total'], Transaction::first()->total);
  }

  public function test_a_clover_webhook_update_partial_full_tender_not_deletes_transaction() {
    factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'open']);
    $url = "/api/webhook/clover";
    $attributes = $this->createPayLoadPartial();
    $headers = $this->cloverWebhookHeader();
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'clover']);
    $cloverAccount = factory(\App\Models\Business\CloverAccount::class)->create(['merchant_id' => 'XYZVJT2ZRRRSC', 'tender_id' => 'fake_tender_id', 'pos_account_id' => $posAccount->id]);
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $this->json('POST', $url, $attributes, $headers);

    $unassignedTransaction = UnassignedTransaction::where('pos_transaction_id', 'partial')->first();
    $unassignedTransaction->customer_id = $customer->id;
    $unassignedTransaction->save();

    $testOrder = CloverTestHelpers::getPartialPaidOrder('partial');
    $this->assertEquals(1, Transaction::count());
    $this->assertEquals(count($testOrder['lineItems']['elements']), PurchasedItem::count());

    $transaction = Transaction::where('pos_transaction_id', 'partial')->first();
    $this->assertEquals($testOrder['payments']['elements'][0]['amount'], $transaction->partial_payment);

    $attributes = $this->updatePartialCompleteTender();
    $this->json('POST', $url, $attributes, $headers);

    $this->assertEquals(1, Transaction::count());
    $this->assertEquals(count($testOrder['lineItems']['elements']), PurchasedItem::count());
    $this->assertEquals($testOrder['total'], Transaction::first()->total);
  }
  
  public function test_a_clover_webhook_delete_removes_transaction() {
    factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'open']);
    factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'paid']);
    $url = "/api/webhook/clover";
    $attributes = $this->createPayLoadInitialNotTender();
    $headers = $this->cloverWebhookHeader();
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'clover']);
    $cloverAccount = factory(\App\Models\Business\CloverAccount::class)->create(['merchant_id' => 'XYZVJT2ZRRRSC', 'tender_id' => 'fake_tender_id', 'pos_account_id' => $posAccount->id]);
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);

    $response = $this->json('POST', $url, $attributes, $headers)->getData();
    $unassignedTransaction = UnassignedTransaction::where('pos_transaction_id', 'not_tender')->first();
    $unassignedTransaction->customer_id = $customer->id;
    $unassignedTransaction->save();

    $this->assertEquals(1, Transaction::count());
    $this->assertEquals(4, PurchasedItem::count());


    $attributes = $this->createPayLoadDelete();
    $response = $this->json('POST', $url, $attributes, $headers)->getData();
    $this->assertEquals(0, Transaction::count());
    $this->assertEquals(0, PurchasedItem::count());
  }

  public function test_a_clover_webhook_delete_not_remove_transaction_if_status_paid() {
    factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'open']);
    $paidStatus = factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'paid']);
    $url = "/api/webhook/clover";
    $attributes = $this->createPayLoadInitialNotTender();
    $headers = $this->cloverWebhookHeader();
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'clover']);
    $cloverAccount = factory(\App\Models\Business\CloverAccount::class)->create(['merchant_id' => 'XYZVJT2ZRRRSC', 'tender_id' => 'fake_tender_id', 'pos_account_id' => $posAccount->id]);
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);

    $response = $this->json('POST', $url, $attributes, $headers)->getData();
    $unassignedTransaction = UnassignedTransaction::where('pos_transaction_id', 'not_tender')->first();
    $unassignedTransaction->customer_id = $customer->id;
    $unassignedTransaction->save();

    $transaction = Transaction::first();
    $transaction->status_id = $paidStatus->id;
    $transaction->save();

    $this->assertEquals(1, Transaction::count());
    $this->assertEquals(4, PurchasedItem::count());


    $attributes = $this->createPayLoadDelete();
    $response = $this->json('POST', $url, $attributes, $headers)->getData();
    $this->assertEquals(1, Transaction::count());
    $this->assertEquals(4, PurchasedItem::count());
  }

  public function test_a_clover_webhook_refund_full_creates_refund() {
    factory(\App\Models\Refund\RefundStatus::class)->create(['name' => 'refund_pending']);
    $status = factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'paid']);
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'clover']);
    $cloverAccount = factory(\App\Models\Business\CloverAccount::class)->create(['merchant_id' => 'XYZVJT2ZRRRSC', 'tender_id' => 'fake_tender_id', 'pos_account_id' => $posAccount->id]);
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $transaction = factory(\App\Models\Transaction\Transaction::class)->create([
      'customer_id' => $customer->id,
      'business_id' => $cloverAccount->posAccount->business_id,
      'status_id' => $status->id,
      'pos_transaction_id' => 'refund_full_id',
    ]);

    $url = "/api/webhook/clover";
    $attributes = $this->createPayLoadRefundFull();
    $headers = $this->cloverWebhookHeader();
    $this->json('POST', $url, $attributes, $headers);
    $this->assertDatabaseHas('refunds', ['transaction_id' => $transaction->id, 'pos_refund_id' => 'MMRTG4BJEP1E4']);
    $testOrder = CloverTestHelpers::getFullRefundOrder('refund_full_id');
    $this->assertEquals($testOrder['total'], Refund::first()->total);
  }

  public function test_a_clover_webhook_refund_partial_creates_refund() {
    factory(\App\Models\Refund\RefundStatus::class)->create(['name' => 'refund_pending']);
    $status = factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'paid']);
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'clover']);
    $cloverAccount = factory(\App\Models\Business\CloverAccount::class)->create(['merchant_id' => 'XYZVJT2ZRRRSC', 'tender_id' => 'fake_tender_id', 'pos_account_id' => $posAccount->id]);
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $transaction = factory(\App\Models\Transaction\Transaction::class)->create([
      'customer_id' => $customer->id,
      'business_id' => $cloverAccount->posAccount->business_id,
      'status_id' => $status->id,
      'pos_transaction_id' => 'refund_partial_id',
    ]);

    $url = "/api/webhook/clover";
    $attributes = $this->createPayLoadRefundPartial();
    $headers = $this->cloverWebhookHeader();
    $this->json('POST', $url, $attributes, $headers);
    $this->assertDatabaseHas('refunds', ['transaction_id' => $transaction->id, 'pos_refund_id' => 'MMRTG4BJEP1E4']);
    $testOrder = CloverTestHelpers::getPartialRefundOrder('refund_partial_id');
    $this->assertNotEquals($testOrder['total'], Refund::first()->total);
  }

  public function test_a_clover_account_transaction_with_an_employee_creates_employee_if_not_stored() {
    factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'open']);
    $url = "/api/webhook/clover";
    $attributes = $this->createPayLoadInitialNotTender();
    $headers = $this->cloverWebhookHeader();
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'clover']);
    $cloverAccount = factory(\App\Models\Business\CloverAccount::class)->create(['merchant_id' => 'XYZVJT2ZRRRSC', 'tender_id' => 'fake_tender_id', 'pos_account_id' => $posAccount->id]);
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $this->json('POST', $url, $attributes, $headers);

    $unassignedTransaction = UnassignedTransaction::where('pos_transaction_id', 'not_tender')->first();
    $unassignedTransaction->customer_id = $customer->id;
    $unassignedTransaction->save();


    $attributes = $this->createPayLoadFinalTender();
    $externalEmployeeId = (CloverTestHelpers::getFinalPaidTender(''))['employee']['id'];
    $this->assertDatabaseMissing('employees', ['external_id' => $externalEmployeeId]);
    $this->json('POST', $url, $attributes, $headers);
    $transaction = \App\Models\Transaction\Transaction::first();
    $status = \App\Models\Transaction\TransactionStatus::where(['name' => 'paid'])->first();
    $transaction->update(['status_id' => $status->id]);
    $this->assertDatabaseHas('employees', ['external_id' => $externalEmployeeId]);
  }

  public function test_a_clover_account_transaction_with_an_employee_does_not_create_employee_if_stored() {
    factory(\App\Models\Transaction\TransactionStatus::class)->create(['name' => 'open']);
    $url = "/api/webhook/clover";
    $attributes = $this->createPayLoadInitialNotTender();
    $headers = $this->cloverWebhookHeader();
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'clover']);
    $cloverAccount = factory(\App\Models\Business\CloverAccount::class)->create(['merchant_id' => 'XYZVJT2ZRRRSC', 'tender_id' => 'fake_tender_id', 'pos_account_id' => $posAccount->id]);
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\CustomerProfile::class)->create(['customer_id' => $customer->id]);
    $this->json('POST', $url, $attributes, $headers);

    $unassignedTransaction = UnassignedTransaction::where('pos_transaction_id', 'not_tender')->first();
    $unassignedTransaction->customer_id = $customer->id;
    $unassignedTransaction->save();


    $attributes = $this->createPayLoadFinalTender();
    $externalEmployeeId = (CloverTestHelpers::getFinalPaidTender(''))['employee']['id'];
    factory(\App\Models\Business\Employee::class)->create(['external_id' => $externalEmployeeId, 'business_id' => $posAccount->business_id]);
    $this->json('POST', $url, $attributes, $headers);
    $transaction = \App\Models\Transaction\Transaction::first();
    $status = \App\Models\Transaction\TransactionStatus::where(['name' => 'paid'])->first();
    $this->assertEquals(1, \App\Models\Business\Employee::count());
    $transaction->update(['status_id' => $status->id]);
    $this->assertEquals(1, \App\Models\Business\Employee::count());
  }






  private function createPayLoadRefundPartial() {
    return [
      "appId" => "DRKVJT2ZRRRSC",
      "merchants" => [
        "XYZVJT2ZRRRSC" => [
          [
            "objectId" => "O:refund_partial_id",
            "type" => "UPDATE",
            "ts" => 1536156558000
          ]
        ],
      ]
    ];
  }

  private function createPayLoadRefundFull() {
    return [
      "appId" => "DRKVJT2ZRRRSC",
      "merchants" => [
        "XYZVJT2ZRRRSC" => [
          [
            "objectId" => "O:refund_full_id",
            "type" => "UPDATE",
            "ts" => 1536156558000
          ]
        ],
      ]
    ];
  }

  private function createPayLoadDelete() {
    return [
      "appId" => "DRKVJT2ZRRRSC",
      "merchants" => [
        "XYZVJT2ZRRRSC" => [
          [
            "objectId" => "O:not_tender",
            "type" => "DELETE",
            "ts" => 1536156558000
          ]
        ],
      ]
    ];
  }

  private function updateNotChange() {
    return [
      "appId" => "DRKVJT2ZRRRSC",
      "merchants" => [
        "XYZVJT2ZRRRSC" => [
          [
            "objectId" => "O:no_change",
            "type" => "UPDATE",
            "ts" => 1536156558000
          ]
        ],
      ]
    ];
  }

  private function updatePartialComplete() {
    return [
      "appId" => "DRKVJT2ZRRRSC",
      "merchants" => [
        "XYZVJT2ZRRRSC" => [
          [
            "objectId" => "O:partial_update_complete",
            "type" => "UPDATE",
            "ts" => 1536156558000
          ]
        ],
      ]
    ];
  }

  private function updatePartialCompleteTender() {
    return [
      "appId" => "DRKVJT2ZRRRSC",
      "merchants" => [
        "XYZVJT2ZRRRSC" => [
          [
            "objectId" => "O:partial_update_complete_tender",
            "type" => "UPDATE",
            "ts" => 1536156558000
          ]
        ],
      ]
    ];
  }

  private function updatePayLoadPartialNotTender() {
    return [
      "appId" => "DRKVJT2ZRRRSC",
      "merchants" => [
        "XYZVJT2ZRRRSC" => [
          [
            "objectId" => "O:partial_update_not_tender",
            "type" => "UPDATE",
            "ts" => 1536156558000
          ]
        ],
      ]
    ];
  }

  private function createPayLoadPartialNotTender() {
    return [
      "appId" => "DRKVJT2ZRRRSC",
      "merchants" => [
        "XYZVJT2ZRRRSC" => [
          [
            "objectId" => "O:partial_update_not_tender",
            "type" => "CREATE",
            "ts" => 1536156558000
          ]
        ],
      ]
    ];
  }

  private function createPayLoadFinalNotTender() {
    return [
      "appId" => "DRKVJT2ZRRRSC",
      "merchants" => [
        "XYZVJT2ZRRRSC" => [
          [
            "objectId" => "O:final_paid_not_tender",
            "type" => "UPDATE",
            "ts" => 1536156558000
          ]
        ],
      ]
    ];
  }

  private function createPayLoadFinalTender() {
    return [
      "appId" => "DRKVJT2ZRRRSC",
      "merchants" => [
        "XYZVJT2ZRRRSC" => [
          [
            "objectId" => "O:final_paid_tender",
            "type" => "UPDATE",
            "ts" => 1536156558000
          ]
        ],
      ]
    ];
  }

  private function createPayLoadInitialNotTender() {
    return [
      "appId" => "DRKVJT2ZRRRSC",
      "merchants" => [
        "XYZVJT2ZRRRSC" => [
          [
            "objectId" => "O:initial_paid_not_tender",
            "type" => "CREATE",
            "ts" => 1536156558000
          ]
        ],
      ]
    ];
  }

  private function updatePayLoadAddItemAddition() {
    return [
      "appId" => "DRKVJT2ZRRRSC",
      "merchants" => [
        "XYZVJT2ZRRRSC" => [
          [
            "objectId" => "O:add_items_addition",
            "type" => "UPDATE",
            "ts" => 1536156558000
          ]
        ],
      ]
    ];
  }

  private function updatePayLoadAddItem() {
    return [
      "appId" => "DRKVJT2ZRRRSC",
      "merchants" => [
        "XYZVJT2ZRRRSC" => [
          [
            "objectId" => "O:add_items_initial",
            "type" => "CREATE",
            "ts" => 1536156558000
          ]
        ],
      ]
    ];
  }

  private function createPayLoadPartialPaid() {
    return [
      "appId" => "DRKVJT2ZRRRSC",
      "merchants" => [
        "XYZVJT2ZRRRSC" => [
          [
            "objectId" => "O:partial_paid_all",
            "type" => "CREATE",
            "ts" => 1536156558000
          ]
        ],
      ]
    ];
  }

  private function createPayLoadPartial() {
    return [
      "appId" => "DRKVJT2ZRRRSC",
      "merchants" => [
        "XYZVJT2ZRRRSC" => [
          [
            "objectId" => "O:partial",
            "type" => "CREATE",
            "ts" => 1536156558000
          ]
        ],
      ]
    ];
  }

  private function createPayLoadPaid() {
    return [
      "appId" => "DRKVJT2ZRRRSC",
      "merchants" => [
        "XYZVJT2ZRRRSC" => [
          [
            "objectId" => "O:paid",
            "type" => "CREATE",
            "ts" => 1536156558000
          ]
        ],
      ]
    ];
  }

  private function createPayLoadToStore() {
    return [
      "appId" => "DRKVJT2ZRRRSC",
      "merchants" => [
        "XYZVJT2ZRRRSC" => [
          [
            "objectId" => "O:not_stored",
            "type" => "CREATE",
            "ts" => 1536156558000
          ]
        ]
      ]
    ];
  }
}
