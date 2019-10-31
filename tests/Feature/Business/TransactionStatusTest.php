<?php

namespace Tests\Feature\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionStatusTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unauth_business_cannot_retrieve_transaction_statuses() {
    $response = $this->json('GET', '/api/business/status/transaction')->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_business_can_retrieve_transaction_statuses() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $this->businessHeaders($business);
    $response = $this->json('GET', '/api/business/status/transaction')->getData();

    $numTransactionStatuses = \App\Models\Transaction\TransactionStatus::count();
    $this->assertEquals($numTransactionStatuses, count($response->data));
  }
}
