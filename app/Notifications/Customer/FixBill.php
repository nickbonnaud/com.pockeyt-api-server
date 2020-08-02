<?php

namespace App\Notifications\Customer;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Transaction\Transaction;
use App\Models\Transaction\TransactionNotification;
use NotificationChannels\OneSignal\OneSignalButton;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;
use App\Jobs\PayTransaction;

class FixBill extends Notification implements ShouldQueue {
  use Queueable;

  private $transaction;

  /**
   * Create a new notification instance.
   *
   * @return void
   */
  public function __construct(Transaction $transaction) {
    $this->transaction = $transaction;
    $this->warningsLeft = $this->setTransactioNotification($transaction);
  }

  /**
   * Get the notification's delivery channels.
   *
   * @param  mixed  $notifiable
   * @return array
   */
  public function via($notifiable) {
    return [OneSignalChannel::class];
  }

  /**
   * Get the mail representation of the notification.
   *
   * @param  mixed  $notifiable
   * @return \Illuminate\Notifications\Messages\MailMessage
   */
  public function toOneSignal($notifiable) {
    $businessName = $this->transaction->business->profile->name;
    $warningsPlurality = $this->warningsLeft == 1 ? "warning" : "warnings";
    $warningsText = $this->warningsLeft > 0 ? "You will be sent {$this->warningsLeft} more {$warningsPlurality} before you are automatically charged." : "This is your last warning before you are automatically charged.";
    return OneSignalMessage::create()
      ->subject("Please resolve issue at {$businessName}.")
      ->body("You have reported an issue with your bill. Please resolve with {$businessName} as soon as possible. {$warningsText}")
      ->setData('transaction_identifier', $this->transaction->identifier)
      ->setData('business_identifier', $this->transaction->business->identifier)
      ->setData("type", 'fix_bill')
      ->setData("warnings_sent", 3 - $this->warningsLeft)
      ->setParameter('ios_category', 'fix_bill')
      ->button(
        OneSignalButton::create('view_bill')
          ->text("View Bill")
      )
      ->button(
        OneSignalButton::create('pay')
          ->text('Pay')
      )
      ->button(
        OneSignalButton::create('call')
          ->text("Call Business")
      );
  }

  /**
   * Get the array representation of the notification.
   *
   * @param  mixed  $notifiable
   * @return array
   */
  public function toArray($notifiable) {
    return [
      'type' => 'exit',
      'customer_id' => $notifiable->id,
      'business_id' => $this->transaction->business->id,
      'transaction_id' => $this->transaction->id
    ];
  }

  private function setTransactioNotification(Transaction $transaction) {
    TransactionNotification::storeNewNotification($transaction->identifier, 'fix_bill');
    $transaction->notification->addWarningSent();
    return 3 - $transaction->notification->number_times_fix_bill_sent;
  }
}
