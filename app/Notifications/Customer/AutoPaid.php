<?php

namespace App\Notifications\Customer;

use Illuminate\Bus\Queueable;
use App\Models\Transaction\Transaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;
use NotificationChannels\OneSignal\OneSignalButton;
use App\Models\Transaction\TransactionNotification;
use Illuminate\Notifications\Notification;

class AutoPaid extends Notification implements ShouldQueue {
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
      ->body("Your bill of {$billTotal} has been automatically paid.")
      ->setData('transaction_identifier', $this->transaction->identifier)
      ->setData('business_identifier', $this->transaction->business->identifier)
      ->setData("type", 'auto_paid')
      ->setParameter('ios_category', 'auto_paid')
      ->button(
        OneSignalButton::create('view_bill')
          ->text("View Bill")
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
      'type' => 'auto_paid',
      'customer_id' => $notifiable->id,
      'business_id' => $this->transaction->business->id,
      'transaction_id' => $this->transaction->id
    ];
  }

  private function setTransactioNotification(Transaction $transaction) {
    TransactionNotification::storeNewNotification($transaction->identifier, 'auto_paid');
  }
}
