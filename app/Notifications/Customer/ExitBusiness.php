<?php

namespace App\Notifications\Customer;

use Illuminate\Bus\Queueable;
use App\Models\Transaction\Transaction;
use Illuminate\Notifications\Notification;
use App\Models\Transaction\TransactionNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\OneSignal\OneSignalButton;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

class ExitBusiness extends Notification implements ShouldQueue {
  use Queueable;

  private $transaction;

  /**
   * Create a new notification instance.
   *
   * @return void
   */
  public function __construct(Transaction $transaction) {
    $this->transaction = $transaction;
    $this->setTransactioNotification($transaction);
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
    return OneSignalMessage::create()
      ->subject("You have exited {$businessName}.")
      ->body("If you would like to keep your bill open, please press the 'Keep Open' button. Otherwise, no action is required if you wish to pay.")
      ->setData('transaction_identifier', $this->transaction->identifier)
      ->setData('business_identifier', $this->transaction->business->identifier)
      ->setData("type", 'exit')
      ->setParameter('ios_category', 'exit')
      ->button(
        OneSignalButton::create('view_bill')
          ->text("View Bill")
      )
      ->button(
        OneSignalButton::create('keep_open')
          ->text("Keep Open")
      )
      ->button(
        OneSignalButton::create('pay')
          ->text('Pay')
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
    TransactionNotification::storeNewNotification($transaction->identifier, 'exit');
  }
}
