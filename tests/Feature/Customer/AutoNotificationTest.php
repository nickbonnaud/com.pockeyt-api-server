<?php

namespace Tests\Feature\Customer;

use App\Services\AutoFixIssueNotification;
use App\Services\AutoPaidWithIssueNotifications;
use App\Services\AutoPaidNotifications;
use App\Services\AutoBillClosedNotifications;
use App\Notifications\Customer\FixBill;
use App\Notifications\Customer\AutoPay;
use App\Notifications\Customer\BillClosed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Transaction\TransactionStatus;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

use Illuminate\Support\Facades\Artisan;

class AutoNotificationTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_send_warning_task_sends_notification() {
    Notification::fake();
    $autoFixIssue = new AutoFixIssueNotification();
    $transaction = factory(\App\Models\Transaction\Transaction::class)->create();
    $transaction->updateStatus(100);

    $notification = factory(\App\Models\Transaction\TransactionNotification::class)->create(['transaction_id' => $transaction->id, 'fix_bill_sent' => false, 'number_times_fix_bill_sent' => 0]);

    $autoFixIssue->send();

    Notification::assertNothingSent();

    $notification->time_fix_bill_sent = now()->subMinutes(15);
    $notification->save();

    $autoFixIssue->send();

    Notification::assertNothingSent();

    $notification->number_times_fix_bill_sent = 3;
    $notification->save();

    $autoFixIssue->send();

    Notification::assertNothingSent();

    $notification->update(['fix_bill_sent' => true, 'number_times_fix_bill_sent' => 1]);

    $autoFixIssue->send();

    Notification::assertNothingSent();

    $transaction->updateStatus(500);

    $autoFixIssue->send();
    
    Notification::assertSentTo(
      [$transaction->customer],
      FixBill::class
    );
  }

  public function test_send_auto_paid_fix_sends_notification() {
    Notification::fake();
    $autoPaidWithIssue = new AutoPaidWithIssueNotifications();
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\AchCustomer::class)->create(['customer_account_id' => $customer->account->id]);
    $business = factory(\App\Models\Business\Business::class)->create();
    factory(\App\Models\Business\AchAccount::class)->create(['account_id' => $business->account->id]);

    $transaction = factory(\App\Models\Transaction\Transaction::class)->create(['customer_id' => $customer->id, 'business_id' => $business->id]);
    $transaction->updateStatus(501);

    $notification = factory(\App\Models\Transaction\TransactionNotification::class)->create(['transaction_id' => $transaction->id, 'fix_bill_sent' => true, 'time_fix_bill_sent' => now(), 'number_times_fix_bill_sent' => 2]);

    $autoPaidWithIssue->send();

    Notification::assertNothingSent();

    $notification->time_fix_bill_sent = now()->subMinutes(30);
    $notification->save();

    $autoPaidWithIssue->send();

    Notification::assertNothingSent();

    $notification->number_times_fix_bill_sent = 3;
    $notification->save();

    $autoPaidWithIssue->send();
    
    Notification::assertSentTo(
      [$transaction->customer],
      AutoPay::class
    );

    $this->assertEquals(103, $transaction->fresh()->status->code);
  }

  public function test_an_auto_pay_notification_sent_after_no_response_from_bill_closed_notif() {
    Notification::fake();
    $autoPaid = new AutoPaidNotifications();
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\AchCustomer::class)->create(['customer_account_id' => $customer->account->id]);
    $business = factory(\App\Models\Business\Business::class)->create();
    factory(\App\Models\Business\AchAccount::class)->create(['account_id' => $business->account->id]);

    $transaction = factory(\App\Models\Transaction\Transaction::class)->create(['customer_id' => $customer->id, 'business_id' => $business->id]);
    $transaction->updateStatus(100);

    $autoPaid->send();

    Notification::assertNothingSent();

    $status = TransactionStatus::where('code', 101)->first();
    $transaction->status()->associate($status);
    $transaction->save();
    $notification = factory(\App\Models\Transaction\TransactionNotification::class)->create(['transaction_id' => $transaction->id, 'bill_closed_sent' => true, 'time_bill_closed_sent' => now()]);

    $autoPaid->send();

    Notification::assertNothingSent();

    $notification->time_bill_closed_sent = now()->subMinutes(19);
    $notification->save();

    $autoPaid->send();

    Notification::assertSentTo(
      [$transaction->customer],
      AutoPay::class
    );

    $this->assertEquals(103, $transaction->fresh()->status->code);
  }

  public function test_an_auto_pay_notification_sent_after_no_response_from_keep_open_notif() {
    Notification::fake();
    $autoPaid = new AutoPaidNotifications();
    $customer = factory(\App\Models\Customer\Customer::class)->create();
    factory(\App\Models\Customer\AchCustomer::class)->create(['customer_account_id' => $customer->account->id]);
    $business = factory(\App\Models\Business\Business::class)->create();
    factory(\App\Models\Business\AchAccount::class)->create(['account_id' => $business->account->id]);

    $transaction = factory(\App\Models\Transaction\Transaction::class)->create(['customer_id' => $customer->id, 'business_id' => $business->id]);
    $transaction->updateStatus(100);

    $autoPaid->send();

    Notification::assertNothingSent();

    $status = TransactionStatus::where('code', 105)->first();
    $transaction->status()->associate($status);
    $transaction->save();
    $notification = factory(\App\Models\Transaction\TransactionNotification::class)->create(['transaction_id' => $transaction->id, 'exit_sent' => true, 'time_exit_sent' => now()]);

    $autoPaid->send();

    Notification::assertNothingSent();

    $notification->time_exit_sent = now()->subMinutes(19);
    $notification->save();

    $autoPaid->send();

    Notification::assertSentTo(
      [$customer],
      AutoPay::class
    );

    $this->assertEquals(103, $transaction->fresh()->status->code);
  }

  public function test_a_keep_open_bill_is_closed_after_20_minutes() {
    Notification::fake();
    $autoClosed = new AutoBillClosedNotifications();
    $transaction = factory(\App\Models\Transaction\Transaction::class)->create();
    $transaction->updateStatus(106);

    $notification = factory(\App\Models\Transaction\TransactionNotification::class)->create(['transaction_id' => $transaction->id, 'exit_sent' => true, 'time_exit_sent' => now()]);

    $autoClosed->send();
    Notification::assertNothingSent();

    $notification->update(['time_exit_sent' => now()->subMinutes(25)]);

    $autoClosed->send();

    Notification::assertSentTo(
      [$transaction->customer],
      BillClosed::class
    );

    $this->assertEquals(101, $transaction->fresh()->status->code);
  }
}
