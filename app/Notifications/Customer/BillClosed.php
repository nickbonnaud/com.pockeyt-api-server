<?php

namespace App\Notifications\Customer;

use Illuminate\Bus\Queueable;
use App\Channels\PushChannel;
use Illuminate\Notifications\Notification;
use App\Models\Transaction\Transaction;
use App\Models\Transaction\TransactionNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\OneSignal\OneSignalButton;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

class BillClosed extends Notification implements ShouldQueue {
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
   * Get the array representation of the notification.
   *
   * @param  mixed  $notifiable
   * @return array
   */
  public function toOneSignal($notifiable) {
    $appName = env('BUSINESS_NAME');
    $businessName = $this->transaction->business->profile->name;
    return OneSignalMessage::create()
      ->subject("You're bill from {$businessName}.")
      ->body("You will be charged {$this->transaction->formatMoney($this->transaction->total)}.")
      ->setData('transaction_identifier', $this->transaction->identifier)
      ->setData('business_identifier', $this->transaction->business->identifier)
      ->setData("type", 'bill_closed')
      ->setParameter('ios_category', 'bill_closed')
      ->button(
        OneSignalButton::create('view_bill')
          ->text("View Bill")
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
      'type' => 'bill_closed',
      'customer_id' => $notifiable->id,
      'business_id' => $this->transaction->business->id,
      'transaction_id' => $this->transaction->id
    ];
  }

  private function setTransactioNotification(Transaction $transaction) {
    TransactionNotification::storeNewNotification($transaction->identifier, 'bill_closed');
  }
}
