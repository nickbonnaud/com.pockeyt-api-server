<?php

namespace Tests\Feature\Transaction;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TransactionIssueTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unauth_customer_cannot_report_a_transaction_issue() {
    $transaction = factory(\App\Models\Transaction\Transaction::class)->create();
    $body = [
      'transaction_identifier' => $transaction->identifier,
      'type' => 'wrong_bill',
      'issue' => $this->faker->sentence,
    ];

    $response = $this->json('POST', '/api/customer/transaction-issue', $body);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_a_customer_must_send_correct_data_to_create_transaction_issue() {
    $transaction = factory(\App\Models\Transaction\Transaction::class)->create();
    $body = [
      'transaction_identifier' => 'not_identifier',
      'type' => 'not_a_type',
      'issue' => "",
    ];

    $this->customerHeaders($transaction->customer);
    $response = $this->json('POST', '/api/customer/transaction-issue', $body)->assertStatus(422);
    $response = $response->getData();
    $this->assertSame("The selected transaction identifier is invalid.", $response->errors->transaction_identifier[0]);
    $this->assertSame("The selected type is invalid.", $response->errors->type[0]);
    $this->assertSame("The issue field is required.", $response->errors->issue[0]);
  }

  public function test_a_customer_must_create_an_issue_for_a_transaction_they_own() {
    $transaction = factory(\App\Models\Transaction\Transaction::class)->create();
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    $body = [
      'transaction_identifier' => $transaction->identifier,
      'type' => 'wrong_bill',
      'issue' => $this->faker->sentence,
    ];

    $this->customerHeaders($customer);
    $response = $this->json('POST', '/api/customer/transaction-issue', $body)->assertStatus(403);
    $this->assertSame('Permission denied.', $response->getData()->errors);
  }

  public function test_a_customer_can_create_an_issue_for_a_transaction() {
    $transaction = $this->createTransaction();
    $body = [
      'transaction_identifier' => $transaction->identifier,
      'type' => 'wrong_bill',
      'issue' => $this->faker->sentence,
    ];

    $this->customerHeaders($transaction->customer);
    $response = $this->json('POST', '/api/customer/transaction-issue', $body)->getData();

    $this->assertDatabaseHas('transaction_issues', ['issue' => $body['issue']]);
    $this->assertSame($body['issue'], $response->data->issue->issue);
  }

  public function test_a_customer_creating_issue_changes_transaction_status() {
    $transaction = $this->createTransaction();
    $body = [
      'transaction_identifier' => $transaction->identifier,
      'type' => 'wrong_bill',
      'issue' => $this->faker->sentence,
    ];

    $this->customerHeaders($transaction->customer);
    $response = $this->json('POST', '/api/customer/transaction-issue', $body)->getData();
    $this->assertSame('500', $response->data->transaction->status->code);
  }

  public function test_an_unauth_customer_cannot_update_issue() {
    $transaction = $this->createTransaction();
    $issue = factory(\App\Models\Transaction\TransactionIssue::class)->create(['transaction_id' => $transaction->id]);

    $this->assertDatabaseHas('transaction_issues', ['identifier' => $issue->identifier, 'type' => 'wrong_bill']);
    $body = [
      'type' => 'error_in_bill',
      'issue' => $this->faker->sentence,
    ];

    $response = $this->json('PATCH', "/api/customer/transaction-issue/{$issue->identifier}", $body)->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_a_customer_must_provide_correct_data_to_update_issue() {
    $transaction = $this->createTransaction();
    $issue = factory(\App\Models\Transaction\TransactionIssue::class)->create(['transaction_id' => $transaction->id]);

    $customer = factory(\App\Models\Customer\Customer::class)->create();

    $this->assertDatabaseHas('transaction_issues', ['identifier' => $issue->identifier, 'type' => 'wrong_bill']);

    $body = [
      'type' => 'not_a_type',
      'issue' => "",
    ];
    $this->customerHeaders($customer);

    $response = $this->json('PATCH', "/api/customer/transaction-issue/{$issue->identifier}", $body)->assertStatus(422);
    $response = $response->getData();
    $this->assertSame("The selected type is invalid.", $response->errors->type[0]);
    $this->assertSame("The issue field is required.", $response->errors->issue[0]);
  }

  public function test_a_customer_must_own_issue_to_update_issue() {
    $transaction = $this->createTransaction();
    $issue = factory(\App\Models\Transaction\TransactionIssue::class)->create(['transaction_id' => $transaction->id]);

    $customer = factory(\App\Models\Customer\Customer::class)->create();

    $this->assertDatabaseHas('transaction_issues', ['identifier' => $issue->identifier, 'type' => 'wrong_bill']);
    $body = [
      'type' => 'error_in_bill',
      'issue' => $this->faker->sentence,
    ];

    $this->customerHeaders($customer);

    $response = $this->json('PATCH', "/api/customer/transaction-issue/{$issue->identifier}", $body)->assertStatus(403);
    $this->assertSame('Permission denied.', $response->getData()->errors);
  }

  public function test_a_customer_can_update_their_issue() {
    $transaction = $this->createTransaction();
    $issue = factory(\App\Models\Transaction\TransactionIssue::class)->create(['transaction_id' => $transaction->id]);

    $this->assertDatabaseHas('transaction_issues', ['identifier' => $issue->identifier, 'type' => 'wrong_bill']);
    $body = [
      'type' => 'error_in_bill',
      'issue' => $this->faker->sentence,
    ];

    $this->customerHeaders($transaction->customer);

    $response = $this->json('PATCH', "/api/customer/transaction-issue/{$issue->identifier}", $body)->getData();
    $this->assertDatabaseHas('transaction_issues', ['identifier' => $issue->identifier, 'type' => 'error_in_bill']);
    $this->assertSame('error_in_bill', $response->data->transaction->issue->type);
  }

  public function test_updating_transaction_error_changes_it_the_status() {
    $transaction = $this->createTransaction();
    $status = \App\Models\Transaction\TransactionStatus::where('code', 500)->first();
    $transaction->update(['status_id' => $status->id]);
    $issue = factory(\App\Models\Transaction\TransactionIssue::class)->create(['transaction_id' => $transaction->id]);

    $this->assertSame($status->code, $transaction->fresh()->status->code);
    $body = [
      'type' => 'other',
      'issue' => $this->faker->sentence,
    ];

    $this->customerHeaders($transaction->customer);

    $response = $this->json('PATCH', "/api/customer/transaction-issue/{$issue->identifier}", $body)->getData();
    $this->assertSame('503', $response->data->transaction->status->code);
  }

  public function test_an_unauth_customer_cannot_delete_an_issue_for_transaction() {
    $transaction = $this->createTransaction();
    $issue = factory(\App\Models\Transaction\TransactionIssue::class)->create(['transaction_id' => $transaction->id]);

    $this->assertDatabaseHas('transaction_issues', ['identifier' => $issue->identifier, 'type' => 'wrong_bill']);

    $response = $this->json('DELETE', "/api/customer/transaction-issue/{$issue->identifier}")->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_a_customer_can_only_delete_an_issue_for_transactions_they_own() {
    $transaction = $this->createTransaction();
    $issue = factory(\App\Models\Transaction\TransactionIssue::class)->create(['transaction_id' => $transaction->id]);
    $customer = factory(\App\Models\Customer\Customer::class)->create();

    $this->assertDatabaseHas('transaction_issues', ['identifier' => $issue->identifier, 'type' => 'wrong_bill']);

    $this->customerHeaders($customer);

    $response = $this->json('DELETE', "/api/customer/transaction-issue/{$issue->identifier}")->assertStatus(403);
    $this->assertSame('Permission denied.', $response->getData()->errors);
  }

  public function test_a_customer_can_delete_an_issue_for_transaction() {
    $transaction = $this->createTransaction();
    $issue = factory(\App\Models\Transaction\TransactionIssue::class)->create(['transaction_id' => $transaction->id]);

    $this->assertDatabaseHas('transaction_issues', ['identifier' => $issue->identifier, 'type' => 'wrong_bill']);

    $this->customerHeaders($transaction->customer);

    $response = $this->json('DELETE', "/api/customer/transaction-issue/{$issue->identifier}")->getData();
    $this->assertNull($response->data->issue);
    $this->assertDatabaseMissing('transaction_issues', ['identifier' => $issue->identifier, 'type' => 'wrong_bill']);
  }

  public function test_deleting_an_issue_for_transaction_sets_status_to_prev_status() {
    $transaction = $this->createTransaction();
    $this->assertSame('100', $transaction->status->code);

    $issue = factory(\App\Models\Transaction\TransactionIssue::class)->create(['transaction_id' => $transaction->id]);
    $status = \App\Models\Transaction\TransactionStatus::where('code', 500)->first();
    $transaction->update(['status_id' => $status->id]);
    $this->assertSame('500', $transaction->fresh()->status->code);

    $this->customerHeaders($transaction->customer);

    $response = $this->json('DELETE', "/api/customer/transaction-issue/{$issue->identifier}")->getData();
    $this->assertSame('100', $response->data->transaction->status->code);
  }


  private function createTransaction() {
    $profilePhotos = factory(\App\Models\Business\ProfilePhotos::class)->create();
    factory(\App\Models\Business\PosAccount::class)->create(['business_id' => $profilePhotos->profile->business_id]);
    $location = factory(\App\Models\Business\Location::class)->create(['business_id' => $profilePhotos->profile->business_id]);
    factory(\App\Models\Business\GeoAccount::class)->create(['location_id' => $location->id]);
    factory(\App\Models\Business\BeaconAccount::class)->create(['location_id' => $location->id]);
    factory(\App\Models\Business\AchAccount::class)->create(['account_id' => $profilePhotos->profile->business->account->id]);

    $transaction = factory(\App\Models\Transaction\Transaction::class)->create(['business_id' => $profilePhotos->profile->business_id]);
    factory(\App\Models\Transaction\PurchasedItem::class, 2)->create(['transaction_id' => $transaction->id]);
    factory(\App\Models\Transaction\PurchasedItem::class, 2)->create(['transaction_id' => $transaction->id, 'item_id' => \App\Models\Business\ActiveItem::first()->id]);
    return $transaction;
  }
}
