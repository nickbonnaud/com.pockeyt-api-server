<?php

namespace Tests\Feature\Transaction;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Notifications\Customer\BillClosed;
use Illuminate\Support\Facades\Notification;

class TransactionTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_closing_a_transaction_sends_closed_bill_notification() {
    Notification::fake();

    $transaction = factory(\App\Models\Transaction\Transaction::class)->create();
    $transaction->closeBill();
    $transaction->customer->notify(new BillClosed($transaction));

    Notification::assertSentTo(
      [$transaction->customer],
      BillClosed::class
    );
  }
}
