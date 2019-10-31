<?php

namespace App\Notifications\Customer;

use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use App\Channels\PushChannel;
use Illuminate\Notifications\Notification;
use App\Models\Transaction\Transaction;
use Illuminate\Contracts\Queue\ShouldQueue;

class BillClosed extends Notification implements ShouldQueue {
  use Queueable;

  public $transaction;
  public $business;

  /**
   * Create a new notification instance.
   *
   * @return void
   */
  public function __construct(Transaction $transaction) {
    $this->transaction = $transaction;
    $this->business = $transaction->business;
  }

  /**
   * Get the notification's delivery channels.
   *
   * @param  mixed  $notifiable
   * @return array
   */
  public function via($notifiable) {
    return [PushChannel::class];
  }

  /**
   * Get the array representation of the notification.
   *
   * @param  mixed  $notifiable
   * @return array
   */
  public function toPush($notifiable) {
    $title = env('BUSINESS_NAME') . 'Payments';
    $body = "Pay $" . $this->transaction->formatMoney($this->transaction->total) . " to " . Str::title($this->business->profile->name) . "?";
    $category = "bill_closed";
    $transactionId = $this->transaction->identifier;
    $businessId = $this->business->identifier;
    $logo = $this->business->profile->photos->logo->small_url;

    if (strtolower($notifiable->pushToken->device) == "ios") {
      return [
        'notification' => [
          'title' => $title,
          'body' => $body,
          'click-action' => $category,
          'sound' => 'default'
        ],
        'data' => [
          'transaction_id' => $transactionId,
          'business_id' => $businessId,
          'category' => $category,
          'logo_url' => $logo,
          'notId' => 1
        ],
        'priority' => 'high'
      ];
    } else {
      return [
        'data' => [
          'customTitle' => $title,
          'customMessage' => $body,
          'sound' => 'default',
          'category' => $category,
          'force-start' => 1,
          'content-available' => 1,
          'no-cache' => 1,
          'custom' => [
            'transaction_id' => $transactionId,
            'business_id' => $businessId,
            'category' => $category,
            'logo_url' => $logo,
          ]
        ]
      ];
    }
  }
}
