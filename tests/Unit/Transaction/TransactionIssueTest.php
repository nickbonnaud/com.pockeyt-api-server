<?php

namespace Tests\Unit\Transaction;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;


class TransactionIssueTest extends TestCase {
	use WithFaker, RefreshDatabase;

	public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_a_transaction_issue_creates_a_unique_identifier() {
    $issue = factory(\App\Models\Transaction\TransactionIssue::class)->create();
    $this->assertNotNull($issue->identifier);
  }

  public function test_a_transaction_issue_belongs_to_a_transaction() {
  	$transaction = factory(\App\Models\Transaction\Transaction::class)->create();
  	$issue = factory(\App\Models\Transaction\TransactionIssue::class)->create(['transaction_id' => $transaction->id]);
  	$this->assertInstanceOf('App\Models\Transaction\Transaction', $issue->transaction);
  }

  public function test_a_transaction_issue_has_one_transaction() {
  	$transaction = factory(\App\Models\Transaction\Transaction::class)->create();
  	$issue = factory(\App\Models\Transaction\TransactionIssue::class)->create(['transaction_id' => $transaction->id]);
  	$this->assertInstanceOf('App\Models\Transaction\TransactionIssue', $transaction->issue);
  }
}
