<?php

namespace Tests\Unit\Business;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EmployeeTest extends TestCase {
	use WithFaker, RefreshDatabase;

	public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

	public function test_an_employee_belongs_to_a_business() {
		$business = factory(\App\Models\Business\Business::class)->create();
		$employee = factory(\App\Models\Business\Employee::class)->create(['business_id' => $business->id]);
		$this->assertInstanceOf('App\Models\Business\Employee', $business->employees->first());
	}

	public function test_a_business_can_have_many_employees() {
		$business = factory(\App\Models\Business\Business::class)->create();
		$employee = factory(\App\Models\Business\Employee::class, 5)->create(['business_id' => $business->id]);
		$this->assertInstanceOf('App\Models\Business\Employee', $business->employees->first());
		$this->assertEquals(5, $business->employees->count());
	}

	public function test_an_employee_has_one_business() {
		$business = factory(\App\Models\Business\Business::class)->create();
		$employee = factory(\App\Models\Business\Employee::class)->create(['business_id' => $business->id]);
		$this->assertInstanceOf('App\Models\Business\Business', $employee->business);
	}

	public function test_an_employee_has_many_transactions() {
		$employee = factory(\App\Models\Business\Employee::class)->create();
		$transactions = factory(\App\Models\Transaction\Transaction::class, 5)->create(['employee_id' => $employee->external_id]);
		$this->assertInstanceOf('App\Models\Transaction\Transaction', $employee->transactions[0]);
	}

	public function test_a_transaction_belongs_to_an_employee() {
		$employee = factory(\App\Models\Business\Employee::class)->create();
		$transaction = factory(\App\Models\Transaction\Transaction::class)->create(['employee_id' => $employee->external_id]);
		$this->assertInstanceOf('App\Models\Business\Employee', $transaction->employee);
	}

	public function test_an_employee_has_many_unassignedTransactions() {
		$employee = factory(\App\Models\Business\Employee::class)->create();
		$unassignedTransactions = factory(\App\Models\Transaction\UnassignedTransaction::class, 5)->create(['employee_id' => $employee->external_id]);
		$this->assertInstanceOf('App\Models\Transaction\UnassignedTransaction', $employee->unassignedTransactions[0]);
	}

	public function test_an_unassignedTransaction_belongs_to_an_employee() {
		$employee = factory(\App\Models\Business\Employee::class)->create();
		$unassignedTransactions = factory(\App\Models\Transaction\UnassignedTransaction::class)->create(['employee_id' => $employee->external_id]);
		$this->assertInstanceOf('App\Models\Business\Employee', $unassignedTransactions->employee);
	}
}
