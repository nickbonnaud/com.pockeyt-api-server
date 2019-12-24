<?php

namespace Tests\Unit\Transaction;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NotificaitonTest extends TestCase {
	use WithFaker, RefreshDatabase;

	public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

	public function test_a_notification_belongs_to_a_transaction() {
		$transaction = factory(\App\Models\Transaction\Transaction::class)->create();
		$notification = factory(\App\Models\Transaction\TransactionNotification::class)->create(['transaction_id' => $transaction->id]);
		$this->assertInstanceOf('App\Models\Transaction\TransactionNotification', $transaction->notification);
	}

	public function test_a_transaction_has_one_notification() {
		$transaction = factory(\App\Models\Transaction\Transaction::class)->create();
		$notification = factory(\App\Models\Transaction\TransactionNotification::class)->create(['transaction_id' => $transaction->id]);
		$this->assertInstanceOf('App\Models\Transaction\Transaction', $notification->transaction);
	}
}
