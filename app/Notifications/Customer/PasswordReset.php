<?php

namespace App\Notifications\Customer;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordReset extends Notification {

  public $resetCode;

  /**
   * Create a new notification instance.
   *
   * @return void
   */
  public function __construct($resetCode) {
    $this->resetCode = $resetCode;
  }

  /**
   * Get the notification's delivery channels.
   *
   * @param  mixed  $notifiable
   * @return array
   */
  public function via($notifiable)
  {
      return ['mail'];
  }

  /**
   * Get the mail representation of the notification.
   *
   * @param  mixed  $notifiable
   * @return \Illuminate\Notifications\Messages\MailMessage
   */
  public function toMail($notifiable) {
    return (new MailMessage)
      ->subject('Reset Password Notification from ' . env('BUSINESS_NAME'))
      ->line('Your Reset Code is: ' . $this->resetCode)
      ->line('This code will expire in 10 minutes')
      ->line('If you did not request a password reset, no further action is required.');
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
          //
      ];
  }
}
