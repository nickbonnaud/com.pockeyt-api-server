<?php

namespace Tests\Unit\Transaction;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TransactionStatusTest extends TestCase {
	use WithFaker, RefreshDatabase;

	public function test_a_transaction_belongs_to_a_transaction_status() {
		$status = factory(\App\Models\Transaction\TransactionStatus::class)->create();
		$transaction = factory(\App\Models\Transaction\Transaction::class)->create(['status_id' => $status->id]);
		$this->assertInstanceOf('App\Models\Transaction\Transaction', $status->transactions->first());
	}

	public function test_a_transaction_status_has_many_transactions() {
		$status = factory(\App\Models\Transaction\TransactionStatus::class)->create();
		$transaction = factory(\App\Models\Transaction\Transaction::class, 2)->create(['status_id' => $status->id]);
		$this->assertEquals(2, $status->transactions->count());
	}

	public function test_a_transaction_has_one_status() {
		$status = factory(\App\Models\Transaction\TransactionStatus::class)->create();
		$transaction = factory(\App\Models\Transaction\Transaction::class)->create(['status_id' => $status->id]);
		$this->assertInstanceOf('App\Models\Transaction\TransactionStatus', $transaction->status);
	}
}
