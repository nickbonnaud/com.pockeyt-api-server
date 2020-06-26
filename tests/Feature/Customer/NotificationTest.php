<?php

namespace Tests\Feature\Customer;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Notifications\Customer\EnterBusiness;
use App\Notifications\Customer\ExitBusiness;
use App\Notifications\Customer\BillClosed;
use App\Notifications\Customer\FixBill;
use App\Notifications\Customer\AutoPay;
use Illuminate\Support\Facades\Notification;

class NotificationTest extends TestCase {
  use WithFaker, RefreshDatabase;

  public function setUp(): void {
    parent::setUp();
    $this->seed();
  }

  public function test_an_enter_push_notification() {
    Notification::fake();
    $customer = factory(\App\Models\Customer\Customer::class)->create(['identifier' => 'fake_identifier_new']);
    $business = factory(\App\Models\Business\Profile::class)->create()->business;
    $business->identifier = 'fake_id_2';
    $business->save();
    $customer->notify(new EnterBusiness($business));

    Notification::assertSentTo(
      [$customer],
      EnterBusiness::class
    );
  }

  public function test_an_enter_notification_not_sent_if_recent() {
    Notification::fake();
    $business = factory(\App\Models\Business\PosAccount::class)->create(['type' => 'other'])->business;
    $location = factory(\App\Models\Business\Location::class)->create(['business_id' => $business->id]);

    $historicLocation = factory(\App\Models\Location\HistoricLocation::class)->create(['location_id' => $location->id]);

    $activeLocation = \App\Models\Location\ActiveLocation::createLocation($historicLocation->customer, $location);
    Notification::assertNothingSent();
    $historicLocation ->delete();
    $activeLocation->delete();

    $historicLocation = \App\Models\Location\HistoricLocation::first();
    $historicLocation->update(['created_at' => now()->subDays(10)]);
    \App\Models\Location\ActiveLocation::createLocation($historicLocation->customer, $location);

    Notification::assertSentTo(
      [$historicLocation->customer],
      EnterBusiness::class
    );
  }

  public function test_an_exit_push_notification() {
    Notification::fake();
    $customer = factory(\App\Models\Customer\Customer::class)->create(['identifier' => 'fake_identifier_new']);
    $business = factory(\App\Models\Business\Profile::class)->create()->business;
    $transaction = factory(\App\Models\Transaction\Transaction::class)->create(['customer_id' => $customer->id, 'business_id' => $business->id]);

    $transaction->identifier = 'fake_open_transaction';
    $transaction->save();
    $transaction->customer->notify(new ExitBusiness($transaction));

    Notification::assertSentTo(
      [$customer],
      ExitBusiness::class
    );

    $this->assertDatabaseHas('transaction_notifications', ['transaction_id' => $transaction->id, 'exit_sent' => true]);
  }

  public function test_a_closed_bill_pushed_notification() {
    Notification::fake();

    $customer = factory(\App\Models\Customer\Customer::class)->create(['identifier' => 'fake_identifier_new']);
    $business = factory(\App\Models\Business\Profile::class)->create()->business;
    $transaction = factory(\App\Models\Transaction\Transaction::class)->create(['customer_id' => $customer->id, 'business_id' => $business->id]);

    $transaction->identifier = 'fake_open_transaction';
    $transaction->save();
    $transaction->updateStatus(101);

    $transaction->customer->notify(new BillClosed($transaction));

    Notification::assertSentTo(
      [$customer],
      BillClosed::class
    );

    $this->assertDatabaseHas('transaction_notifications', ['transaction_id' => $transaction->id, 'bill_closed_sent' => true]);
  }

  public function test_a_fixed_bill_notification() {
    Notification::fake();

    $customer = factory(\App\Models\Customer\Customer::class)->create(['identifier' => 'fake_identifier_new']);
    $business = factory(\App\Models\Business\Profile::class)->create()->business;
    $transaction = factory(\App\Models\Transaction\Transaction::class)->create(['customer_id' => $customer->id, 'business_id' => $business->id]);

    $transaction->identifier = 'fake_open_transaction';
    $transaction->save();

    $transaction->customer->notify(new FixBill($transaction));

    Notification::assertSentTo(
      [$customer],
      FixBill::class
    );

    $this->assertDatabaseHas('transaction_notifications', ['transaction_id' => $transaction->id, 'fix_bill_sent' => true]);
  }

  public function test_an_auto_pay_notification() {
    Notification::fake();

    $customer = factory(\App\Models\Customer\Customer::class)->create(['identifier' => 'fake_identifier_new']);
    $business = factory(\App\Models\Business\Profile::class)->create()->business;
    $transaction = factory(\App\Models\Transaction\Transaction::class)->create(['customer_id' => $customer->id, 'business_id' => $business->id]);

    $transaction->identifier = 'fake_open_transaction';
    $transaction->save();

    $transaction->customer->notify(new AutoPay($transaction));

    Notification::assertSentTo(
      [$customer],
      AutoPay::class
    );

    $this->assertDatabaseHas('transaction_notifications', ['transaction_id' => $transaction->id, 'auto_pay_sent' => true]);
  }
}
