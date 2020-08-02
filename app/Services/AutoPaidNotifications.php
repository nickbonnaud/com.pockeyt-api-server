<?php

namespace App\Services;

use App\Models\Transaction\Transaction;
use App\Notifications\Customer\AutoPaid;

class AutoPaidNotifications {

	public function send() {
		$transactions = Transaction::whereHas('status', function($q) {
			$q->whereIn('code', [101, 105]);
		})->whereHas('notification', function($q) {
			$q->where(function($q) {
				$q->where('bill_closed_sent', true)
					->whereTime('time_bill_closed_sent', '<=',  now()->subMinutes(10));
			})->orWhere(function($q) {
				$q->where('exit_sent', true)
					->whereTime('time_exit_sent', '<=',  now()->subMinutes(10));
			});
		})->get();

		foreach ($transactions as $transaction) {
			$transaction->customer->notify(new AutoPaid($transaction));
			$transaction->updateStatus(104);
		}
	}
}