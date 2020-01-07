<?php

namespace Tests\Unit\Transaction;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RefundTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_a_refund_generates_a_unique_identifier() {
    $refund = factory(\App\Models\Refund\Refund::class)->create();
    $this->assertNotNull($refund->identifier);
  }

  public function test_a_refund_belongs_to_a_transaction() {
  	$transaction = factory(\App\Models\Transaction\Transaction::class)->create();
  	$refund = factory(\App\Models\Refund\Refund::class)->create(['transaction_id' => $transaction->id]);
  	$this->assertInstanceOf('App\Models\Refund\Refund', $transaction->refunds->first());
  }

  public function test_a_transaction_can_have_many_refunds() {
  	$transaction = factory(\App\Models\Transaction\Transaction::class)->create();
  	$refund = factory(\App\Models\Refund\Refund::class, 2)->create(['transaction_id' => $transaction->id]);
  	$this->assertEquals(2, $transaction->refunds->count());
  }

  public function test_a_refund_has_one_transaction() {
  	$transaction = factory(\App\Models\Transaction\Transaction::class)->create();
  	$refund = factory(\App\Models\Refund\Refund::class)->create(['transaction_id' => $transaction->id]);
  	$this->assertInstanceOf('App\Models\Transaction\Transaction', $refund->transaction);
  }
}
