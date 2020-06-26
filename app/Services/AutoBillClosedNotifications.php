<?php

namespace App\Services;

use App\Models\Transaction\Transaction;

class AutoBillClosedNotifications {

	public function send() {
		$transactions = Transaction::whereHas('status', function($q) {
			$q->where('code', 106);
		})->whereHas('notification', function($q) {
			$q->where(function($q) {
				$q->where('exit_sent', true)
					->whereTime('time_exit_sent', '<=',  now()->subMinutes(20));
			});
		})->get();

		foreach ($transactions as $transaction) {
			$transaction->updateStatus(101);
		}
	}
}