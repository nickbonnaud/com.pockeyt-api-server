<?php

namespace App\Observers\Transaction;

use App\Models\Transaction\TransactionNotification;

class TransactionNotificationObserver {
  
  public function saving(TransactionNotification $transactionNotification) {
  	if ($transactionNotification->last == 'fix') {
  		$transactionNotification->number_times_fix_sent = $transactionNotification->number_times_fix_sent + 1;
  	}
  }
}
