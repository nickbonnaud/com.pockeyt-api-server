<?php

namespace App\Notifications\Customer;

use Illuminate\Bus\Queueable;
use App\Models\Business\Business;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\OneSignal\OneSignalChannel;
use NotificationChannels\OneSignal\OneSignalMessage;

class EnterBusiness extends Notification implements ShouldQueue {
  use Queueable;

  private $business;

  /**
   * Create a new notification instance.
   *
   * @return void
   */
  public function __construct(Business $business) {
    $this->business = $business;
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
    $appName = env('BUSINESS_NAME');
    $businessName = $this->business->profile->name;
    return OneSignalMessage::create()
      ->subject("Welcome to {$businessName}!")
      ->body("You can use {$appName} to pay at {$businessName}.")
      ->setData("business_identifier", $this->business->identifier)
      ->setData("type", 'enter');
  }

  /**
   * Get the array representation of the notification.
   *
   * @param  mixed  $notifiable
   * @return array
   */
  public function toArray($notifiable)
  {
    return [
      'type' => 'enter',
      'customer_id' => $notifiable->id,
      'business_id' => $this->business->id
    ];
  }
}
