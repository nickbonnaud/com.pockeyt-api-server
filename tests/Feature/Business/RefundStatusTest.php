<?php

namespace Tests\Feature\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RefundStatusTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unauth_business_cannot_retrieve_refund_statuses() {
    $response = $this->json('GET', '/api/business/status/refund')->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_busines_can_retrieve_refund_statuses() {
    $business = factory(\App\Models\Business\Business::class)->create();
    $this->businessHeaders($business);
    $response = $this->json('GET', '/api/business/status/refund')->getData();
    $numRefundStatuses = \App\Models\Refund\RefundStatus::count();
    $this->assertEquals($numRefundStatuses, count($response->data));
  }
}
