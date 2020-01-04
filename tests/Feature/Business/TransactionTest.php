<?php

namespace Tests\Feature\Business;

use Tests\TestCase;
use Carbon\Carbon;
use App\Models\Transaction\TransactionStatus;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Illuminate\Support\Facades\DB;

class TransactionTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_unauth_business_cannot_request_transactions() {
    $transactions = factory(\App\Models\Transaction\Transaction::class, 12)->create();
    $response = $this->json('GET', '/api/business/transactions')->assertStatus(401);
    $this->assertEquals('Unauthenticated.', ($response->getData())->message);
  }

  public function test_an_auth_business_can_request_transactions_base() {
    $transaction = factory(\App\Models\Transaction\Transaction::class)->create(['customer_id' => $this->createCustomer()->id]);
    $numTransactions = 11;
    factory(\App\Models\Transaction\Transaction::class, $numTransactions)->create(['business_id' => $transaction->business_id, 'customer_id' => $this->createCustomer()->id]);
    $headers = $this->businessHeaders($transaction->business);
    $response = $this->json('GET', '/api/business/transactions?recent=true')->getData();
    $this->assertEquals($response->meta->total, $numTransactions + 1);
  }

  public function test_an_auth_business_only_retrieves_its_transactions() {
    $transaction = factory(\App\Models\Transaction\Transaction::class)->create(['customer_id' => $this->createCustomer()->id]);
    $numTransactions = 4;
    factory(\App\Models\Transaction\Transaction::class, $numTransactions)->create(['business_id' => $transaction->business_id, 'customer_id' => $this->createCustomer()->id]);
    factory(\App\Models\Transaction\Transaction::class, 6)->create(['customer_id' => $this->createCustomer()->id]);

    $headers = $this->businessHeaders($transaction->business);
    $response = $this->json('GET', '/api/business/transactions?recent=true')->getData();
    $this->assertEquals($response->meta->total, $numTransactions + 1);
  }

  public function test_an_auth_business_can_request_transactions_order_by_recent() {
    $transaction = factory(\App\Models\Transaction\Transaction::class)->create(['customer_id' => $this->createCustomer()->id]);
    $numTransactions = 15;
    $i = 1;
    while ($i < $numTransactions) {
      $transactionLast = factory(\App\Models\Transaction\Transaction::class)->create(['business_id' => $transaction->business_id, 'created_at' => now()->subDays($i), 'customer_id' => $this->createCustomer()->id]);
      $i++;
    }
    
    $headers = $this->businessHeaders($transaction->business);
    $response = $this->json('GET', '/api/business/transactions?recent=true')->getData();
    $this->assertEquals($response->meta->total, $numTransactions);
    $this->assertEquals($transaction->identifier, $response->data[0]->transaction->identifier);

    $nextLink = $response->links->next;
    while ($nextLink) {
      $response = $this->json('GET', $nextLink)->getData();
      $nextLink = $response->links->next;
    }
    $this->assertEquals($transactionLast->identifier, $response->data[count($response->data) - 1]->transaction->identifier);
  }

  public function test_an_auth_business_can_request_transactions_by_status() {
    Notification::fake();
    $openStatusId = TransactionStatus::where('code', 100)->first()->id;
    $transactionOpen = factory(\App\Models\Transaction\Transaction::class)->create(['status_id' => $openStatusId, 'customer_id' => $this->createCustomer()->id]);

    $numOpenTransactions = 12;
    factory(\App\Models\Transaction\Transaction::class, $numOpenTransactions)->create(['status_id' => $openStatusId, 'business_id' => $transactionOpen->business_id, 'customer_id' => $this->createCustomer()->id]);

    $numClosedTransactions = 5;
    $closedStatusId =  TransactionStatus::where('code', 101)->first()->id;
    factory(\App\Models\Transaction\Transaction::class, $numClosedTransactions)->create(['status_id' => $closedStatusId, 'business_id' => $transactionOpen->business_id, 'customer_id' => $this->createCustomer()->id]);

    $this->businessHeaders($transactionOpen->business);
    $response = $this->json('GET', '/api/business/transactions?status=100')->getData();
    $this->assertEquals($response->meta->total, $numOpenTransactions + 1);
  }

  public function test_an_auth_business_can_request_transactions_by_customer() {
    $customerTransaction = factory(\App\Models\Transaction\Transaction::class)->create(['customer_id' => $this->createCustomer()->id]);
    $numCustomerTransaction = 11;
    factory(\App\Models\Transaction\Transaction::class, $numCustomerTransaction)->create(['customer_id' => $customerTransaction->customer->id, 'business_id' => $customerTransaction->business_id]);

    $nonCustomerTransactions = 10;
    factory(\App\Models\Transaction\Transaction::class, $nonCustomerTransactions)->create(['business_id' => $customerTransaction->business_id, 'customer_id' => $this->createCustomer()->id]);

    $this->businessHeaders($customerTransaction->business);
    $response = $this->json('GET', "/api/business/transactions?customer={$customerTransaction->customer->identifier}")->getData();
    $this->assertEquals($response->meta->total, $numCustomerTransaction + 1);
  }

  public function test_an_auth_business_can_request_transactions_by_date() {
    $startDate = urlencode(Carbon::now()->subDays(5)->toIso8601String());
    $endDate = urlencode(Carbon::now()->subDays(2)->toIso8601String());
    $inDateTransaction = factory(\App\Models\Transaction\Transaction::class)->create(['created_at' => Carbon::now()->subDays(4), 'customer_id' => $this->createCustomer()->id]);
    $numInDateTransactionsFirst = 7;
    factory(\App\Models\Transaction\Transaction::class, $numInDateTransactionsFirst)->create(['created_at' => Carbon::now()->subDays(4), 'business_id' => $inDateTransaction->business_id, 'customer_id' => $this->createCustomer()->id]);

    $numInDateTransactionsSecond = 8;
    factory(\App\Models\Transaction\Transaction::class, $numInDateTransactionsSecond)->create(['created_at' => Carbon::now()->subDays(3), 'business_id' => $inDateTransaction->business_id, 'customer_id' => $this->createCustomer()->id]);

    factory(\App\Models\Transaction\Transaction::class, 12)->create(['created_at' => Carbon::now()->subDays(1), 'business_id' => $inDateTransaction->business_id, 'customer_id' => $this->createCustomer()->id]);

    $this->businessHeaders($inDateTransaction->business);
    $response = $this->json('GET', "/api/business/transactions?date[]={$startDate}&date[]={$endDate}")->getData();
    $this->assertEquals($response->meta->total, $numInDateTransactionsFirst + $numInDateTransactionsSecond +  1);
  }

  public function test_an_auth_business_can_request_transactions_by_employee() {
    $employeeId = 'cdb288y3';
    $employeeTransaction = factory(\App\Models\Transaction\Transaction::class)->create(['employee_id' => $employeeId, 'customer_id' => $this->createCustomer()->id]);
    $numEmployeeTransaction = 17;
    factory(\App\Models\Transaction\Transaction::class, $numEmployeeTransaction)->create(['employee_id' => $employeeId, 'business_id' => $employeeTransaction->business_id, 'customer_id' => $this->createCustomer()->id]);

    $nonEmployeeTransactions = 10;
    factory(\App\Models\Transaction\Transaction::class, $nonEmployeeTransactions)->create(['employee_id' => 'cdjnisjdijd3', 'business_id' => $employeeTransaction->business_id, 'customer_id' => $this->createCustomer()->id]);

    $this->businessHeaders($employeeTransaction->business);
    $response = $this->json('GET', "/api/business/transactions?employee={$employeeId}")->getData();
    $this->assertEquals($response->meta->total, $numEmployeeTransaction + 1);
  }

  public function test_an_auth_business_can_use_multiple_queries_request_transaction() {
    $startDate = urlencode(Carbon::now()->subDays(6)->toIso8601String());
    $endDate = urlencode(Carbon::now()->subDays(3)->toIso8601String());
    $correctStatusId = TransactionStatus::where('code', 200)->first()->id;
    $incorrectStatusId = TransactionStatus::where('code', 100)->first()->id;

    $correctTransaction = factory(\App\Models\Transaction\Transaction::class)->create(['created_at' => Carbon::now()->subDays(4), 'status_id' => $correctStatusId, 'customer_id' => $this->createCustomer()->id]);

    $numCorrectTransactions = 5;
    factory(\App\Models\Transaction\Transaction::class, $numCorrectTransactions)->create(['created_at' => Carbon::now()->subDays(5), 'business_id' => $correctTransaction->business_id, 'customer_id' => $correctTransaction->customer_id, 'status_id' => $correctStatusId]);

    factory(\App\Models\Transaction\Transaction::class, 2)->create(['created_at' => Carbon::now()->subDays(5), 'business_id' => $correctTransaction->business_id, 'customer_id' => $correctTransaction->customer_id, 'status_id' => $incorrectStatusId]);

    factory(\App\Models\Transaction\Transaction::class, 3)->create(['created_at' => Carbon::now()->subDays(5), 'business_id' => $correctTransaction->business_id, 'status_id' => $correctStatusId, 'customer_id' => $this->createCustomer()->id]);

    factory(\App\Models\Transaction\Transaction::class, 6)->create(['created_at' => Carbon::now()->subDays(10), 'business_id' => $correctTransaction->business_id, 'customer_id' => $correctTransaction->customer_id, 'status_id' => $correctStatusId]);


    $this->businessHeaders($correctTransaction->business);
    $response = $this->json('GET', "/api/business/transactions?date[]={$startDate}&date[]={$endDate}&status=200&customer={$correctTransaction->customer->identifier}")->getData();
    $this->assertEquals($response->meta->total, $numCorrectTransactions +  1);
  }

  public function test_an_auth_business_can_request_sales_data_net_sales() {
    $startDate = urlencode(Carbon::now()->subDays(6)->toIso8601String());
    $endDate = urlencode(Carbon::now()->subDays(3)->toIso8601String());

    $correctTransaction = factory(\App\Models\Transaction\Transaction::class)->create(['created_at' => Carbon::now()->subDays(4)]);

    $numCorrectTransactions = 7;
    $correctTransactions = factory(\App\Models\Transaction\Transaction::class, $numCorrectTransactions)->create(['created_at' => Carbon::now()->subDays(5), 'business_id' => $correctTransaction->business_id]);

    factory(\App\Models\Transaction\Transaction::class, 12)->create(['created_at' => Carbon::now()->subDays(1), 'business_id' => $correctTransaction->business_id]);

    $this->businessHeaders($correctTransaction->business);
    $response = $this->json('GET', "/api/business/transactions?date[]={$startDate}&date[]={$endDate}&sum=net_sales")->getData();
    
    $total = $correctTransaction->net_sales;
    foreach ($correctTransactions as $transaction) {
      $total = $total + $transaction->net_sales;
    }
    $this->assertEquals($response->data->sales_data, $total);
  }

  public function test_an_auth_business_can_request_sales_data_net_total() {
    $startDate = urlencode(Carbon::now()->subDays(10)->toIso8601String());
    $endDate = urlencode(Carbon::now()->subDays(7)->toIso8601String());

    $correctTransaction = factory(\App\Models\Transaction\Transaction::class)->create(['created_at' => Carbon::now()->subDays(8)]);

    $numCorrectTransactions = 21;
    $correctTransactions = factory(\App\Models\Transaction\Transaction::class, $numCorrectTransactions)->create(['created_at' => Carbon::now()->subDays(9), 'business_id' => $correctTransaction->business_id]);

    factory(\App\Models\Transaction\Transaction::class, 12)->create(['created_at' => Carbon::now()->subDays(3), 'business_id' => $correctTransaction->business_id]);

    $this->businessHeaders($correctTransaction->business);
    $response = $this->json('GET', "/api/business/transactions?date[]={$startDate}&date[]={$endDate}&sum=total")->getData();
    
    $total = $correctTransaction->total;
    foreach ($correctTransactions as $transaction) {
      $total = $total + $transaction->total;
    }
    $this->assertEquals($response->data->sales_data, $total);
  }

  public function test_an_auth_business_can_request_sales_data_net_tax() {
    $startDate = urlencode(Carbon::now()->subDays(10)->toIso8601String());
    $endDate = urlencode(Carbon::now()->subDays(7)->toIso8601String());

    $correctTransaction = factory(\App\Models\Transaction\Transaction::class)->create(['created_at' => Carbon::now()->subDays(8)]);

    $numCorrectTransactions = 21;
    $correctTransactions = factory(\App\Models\Transaction\Transaction::class, $numCorrectTransactions)->create(['created_at' => Carbon::now()->subDays(9), 'business_id' => $correctTransaction->business_id]);

    factory(\App\Models\Transaction\Transaction::class, 12)->create(['created_at' => Carbon::now()->subDays(3), 'business_id' => $correctTransaction->business_id]);

    $this->businessHeaders($correctTransaction->business);
    $response = $this->json('GET', "/api/business/transactions?date[]={$startDate}&date[]={$endDate}&sum=tax")->getData();
    
    $total = $correctTransaction->tax;
    foreach ($correctTransactions as $transaction) {
      $total = $total + $transaction->tax;
    }
    $this->assertEquals($response->data->sales_data, $total);
  }

  public function test_an_auth_business_can_request_sales_data_net_tips() {
    $startDate = urlencode(Carbon::now()->subDays(10)->toIso8601String());
    $endDate = urlencode(Carbon::now()->subDays(7)->toIso8601String());

    $correctTransaction = factory(\App\Models\Transaction\Transaction::class)->create(['created_at' => Carbon::now()->subDays(8)]);

    $numCorrectTransactions = 21;
    $correctTransactions = factory(\App\Models\Transaction\Transaction::class, $numCorrectTransactions)->create(['created_at' => Carbon::now()->subDays(9), 'business_id' => $correctTransaction->business_id]);

    factory(\App\Models\Transaction\Transaction::class, 12)->create(['created_at' => Carbon::now()->subDays(3), 'business_id' => $correctTransaction->business_id]);

    $this->businessHeaders($correctTransaction->business);
    $response = $this->json('GET', "/api/business/transactions?date[]={$startDate}&date[]={$endDate}&sum=tip")->getData();
    $total = $correctTransaction->tip;
    foreach ($correctTransactions as $transaction) {
      $total = $total + $transaction->tip;
    }
    $this->assertEquals($response->data->sales_data, $total);
  }

  public function test_an_auth_business_can_request_tips_by_employee() {
    $startDate = urlencode(Carbon::now()->subDays(10)->toIso8601String());
    $endDate = urlencode(Carbon::now()->subDays(7)->toIso8601String());
    $correctEmployeeId = '3cycd63rbfc';

    $correctTransaction = factory(\App\Models\Transaction\Transaction::class)->create(['created_at' => Carbon::now()->subDays(8), 'employee_id' => $correctEmployeeId]);

    $numCorrectTransactions = 15;
    $correctTransactions = factory(\App\Models\Transaction\Transaction::class, $numCorrectTransactions)->create(['created_at' => Carbon::now()->subDays(9), 'business_id' => $correctTransaction->business_id, 'employee_id' => $correctEmployeeId]);

    factory(\App\Models\Transaction\Transaction::class, 12)->create(['created_at' => Carbon::now()->subDays(3), 'business_id' => $correctTransaction->business_id, 'employee_id' => $correctEmployeeId]);

    factory(\App\Models\Transaction\Transaction::class, 12)->create(['created_at' => Carbon::now()->subDays(9), 'business_id' => $correctTransaction->business_id, 'employee_id' => '4324325vdsv']);

    $this->businessHeaders($correctTransaction->business);
    $response = $this->json('GET', "/api/business/transactions?date[]={$startDate}&date[]={$endDate}&employee={$correctEmployeeId}&sum=tip")->getData();
    
    $total = $correctTransaction->tip;
    foreach ($correctTransactions as $transaction) {
      $total = $total + $transaction->tip;
    }
    $this->assertEquals($response->data->sales_data, $total);
  }

  public function test_a_business_can_request_list_of_employees_with_sum_tips() {
    $statusId = TransactionStatus::where('code', 200)->first()->id;
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'other']);
    $business = $posAccount->business;
    $numEmployees = 12;
    $employees = factory(\App\Models\Business\Employee::class, $numEmployees)->create(['business_id' => $business->id]);
    foreach ($employees as $employee) {
      factory(\App\Models\Transaction\Transaction::class, 5)->create(['employee_id' => $employee->external_id, 'business_id' => $business->id, 'status_id' => $statusId]);
    }
    $this->businessHeaders($business);
    $response = $this->json('GET', "/api/business/tips?employees=all")->getData();
    $this->assertEquals($numEmployees, $response->meta->total);
    
    $totalTips = \App\Models\Transaction\Transaction::where(['business_id' => $business->id])->sum('tip');
    $totalEmployeeTips = 0;
    foreach ($response->data as $tipData) {
      $totalEmployeeTips = $totalEmployeeTips + $tipData->tips;
    }
    $this->assertEquals($totalTips, $totalEmployeeTips);
  }

  public function test_a_business_can_only_request_tips_for_their_business() {
    $statusId = TransactionStatus::where('code', 200)->first()->id;
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'other']);
    $business = $posAccount->business;
    $numEmployees = 7;
    $employees = factory(\App\Models\Business\Employee::class, $numEmployees)->create(['business_id' => $business->id]);
    foreach ($employees as $employee) {
      factory(\App\Models\Transaction\Transaction::class, 5)->create(['employee_id' => $employee->external_id, 'business_id' => $business->id, 'status_id' => $statusId]);
    }

    $notPosAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'other']);
    $notBusiness = $notPosAccount->business;
    $notNumEmployees = 5;
    $notEmployees = factory(\App\Models\Business\Employee::class, $notNumEmployees)->create(['business_id' => $notBusiness->id]);

    foreach ($notEmployees as $employee) {
      factory(\App\Models\Transaction\Transaction::class, 5)->create(['employee_id' => $employee->external_id, 'business_id' => $notBusiness->id, 'status_id' => $statusId]);
    }

    $this->businessHeaders($business);
    $response = $this->json('GET', "/api/business/tips?employees=all")->getData();
    $this->assertEquals($numEmployees, $response->meta->total);
    
    $totalTips = \App\Models\Transaction\Transaction::where(['business_id' => $business->id])->sum('tip');
    $totalEmployeeTips = 0;
    foreach ($response->data as $tipData) {
      $totalEmployeeTips = $totalEmployeeTips + $tipData->tips;
    }
    $this->assertEquals($totalTips, $totalEmployeeTips);
  }

  public function test_a_business_can_request_tips_for_employees_by_date() {
    $startDate = urlencode(Carbon::now()->subDays(6)->toIso8601String());
    $endDate = urlencode(Carbon::now()->subDays(2)->toIso8601String());

    $statusId = TransactionStatus::where('code', 200)->first()->id;
    $posAccount = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'other']);
    $business = $posAccount->business;
    $numEmployees = 9;
    $employees = factory(\App\Models\Business\Employee::class, $numEmployees)->create(['business_id' => $business->id]);
    foreach ($employees as $employee) {
      factory(\App\Models\Transaction\Transaction::class, 3)->create(['employee_id' => $employee->external_id, 'business_id' => $business->id, 'status_id' => $statusId, 'created_at' => Carbon::now()->subDays(5)]);
    }

    foreach ($employees as $employee) {
      factory(\App\Models\Transaction\Transaction::class, 2)->create(['employee_id' => $employee->external_id, 'business_id' => $business->id, 'status_id' => $statusId, 'created_at' => Carbon::now()->subDays(4)]);
    }

    foreach ($employees as $employee) {
      factory(\App\Models\Transaction\Transaction::class, 5)->create(['employee_id' => $employee->external_id, 'business_id' => $business->id, 'status_id' => $statusId, 'created_at' => Carbon::now()->subDays(3)]);
    }

    foreach ($employees as $employee) {
      factory(\App\Models\Transaction\Transaction::class, 5)->create(['employee_id' => $employee->external_id, 'business_id' => $business->id, 'status_id' => $statusId, 'created_at' => Carbon::now()->subDays(8)]);
    }

    $employeeOutOfDate = factory(\App\Models\Business\Employee::class)->create(['business_id' => $business->id]);
    factory(\App\Models\Transaction\Transaction::class, 7)->create(['employee_id' => $employeeOutOfDate->external_id, 'business_id' => $business->id, 'status_id' => $statusId, 'created_at' => Carbon::now()->subDays(1)]);

    $this->businessHeaders($business);
    $response = $this->json('GET', "/api/business/tips?employees=all&date[]={$startDate}&date[]={$endDate}")->getData();
    $this->assertEquals($numEmployees, $response->meta->total);
    
    $totalTips = \App\Models\Transaction\Transaction::where(['business_id' => $business->id])->whereBetween('created_at', [$startDate, $endDate])->sum('tip');
    $totalEmployeeTips = 0;
    foreach ($response->data as $tipData) {
      $totalEmployeeTips = $totalEmployeeTips + $tipData->tips;
    }
    $this->assertEquals($totalTips, $totalEmployeeTips);
    $this->assertNotEquals( \App\Models\Transaction\Transaction::where(['business_id' => $business->id])->sum('tip'), $totalTips);
    $this->assertNotEquals( \App\Models\Business\Employee::where(['business_id' => $business->id])->count(), $response->meta->total);
  }

  public function test_a_business_can_retrieve_transactions_by_customer_name() {
    $transaction = factory(\App\Models\Transaction\Transaction::class)->create(['customer_id' => $this->createCustomer()->id]);
    $business = $transaction->business;
    $customer = $transaction->customer;

    $numTransactions = 8;
    factory(\App\Models\Transaction\Transaction::class, $numTransactions)->create(['business_id' => $business->id, 'customer_id' => $customer->id]);

    factory(\App\Models\Transaction\Transaction::class, 6)->create();

    $headers = $this->businessHeaders($business);
    $response = $this->json('GET', "/api/business/transactions?customerFirst={$customer->profile->first_name}&customerLast={$customer->profile->last_name}")->getData();
    $this->assertEquals($response->meta->total, $numTransactions + 1);
  }

  public function test_a_business_can_retrieve_transaction_by_identifier() {
    $business = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'other'])->business;
    $employee = factory(\App\Models\Business\Employee::class)->create(['business_id' => $business->id]);
    $transaction = factory(\App\Models\Transaction\Transaction::class)->create(['business_id' => $business->id, 'employee_id' => $employee->external_id, 'customer_id' => $this->createCustomer()->id]);
    $purchasedItems = factory(\App\Models\Transaction\PurchasedItem::class, 4)->create(['transaction_id' => $transaction->id]);

    $business = $transaction->business;
    $customer = $transaction->customer;

    $numTransactions = 3;
    factory(\App\Models\Transaction\Transaction::class, $numTransactions)->create(['business_id' => $business->id, 'customer_id' => $customer->id]);

    factory(\App\Models\Transaction\Transaction::class, 6)->create();

    $headers = $this->businessHeaders($business);
    $response = $this->json('GET', "/api/business/transactions?id={$transaction->identifier}")->getData();
    $this->assertEquals($transaction->identifier, $response->data[0]->transaction->identifier);
    $this->assertEquals(1, $response->meta->total);
  }


  private function createCustomer() {
    return factory(\App\Models\Customer\CustomerProfilePhoto::class)->create()->profile->customer;
  }
}
