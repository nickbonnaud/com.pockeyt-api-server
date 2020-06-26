<?php

namespace App\Notifications\Customer;

use Illuminate\Bus\Queueable;
use App\Models\Transaction\Transaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;
use App\Models\Transaction\TransactionNotification;
use Illuminate\Notifications\Notification;

class AutoPay extends Notification implements ShouldQueue {
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
    $billTotal = $this->transaction->formatMoney($this->transaction->total);

    return OneSignalMessage::create()
      ->subject("Bill paid at {$businessName}.")
      ->body("You're bill of {$billTotal} has been automatically paid.")
      ->setData('transaction_identifier', $this->transaction->identifier)
      ->setData('business_identifier', $this->transaction->business->identifier)
      ->setData("type", 'auto_pay')
      ->setParameter('ios_category', 'auto_pay');
  }

  /**
   * Get the array representation of the notification.
   *
   * @param  mixed  $notifiable
   * @return array
   */
  public function toArray($notifiable) {
    return [
      'type' => 'auto_pay',
      'customer_id' => $notifiable->id,
      'business_id' => $this->transaction->business->id,
      'transaction_id' => $this->transaction->id
    ];
  }

  private function setTransactioNotification(Transaction $transaction) {
    TransactionNotification::storeNewNotification($transaction->identifier, 'auto_pay');
  }
}
