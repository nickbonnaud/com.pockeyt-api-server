<?php

namespace App\Services;

use App\Models\Transaction\Transaction;
use App\Notifications\Customer\FixBill;

class AutoFixIssueNotification {

	public function send() {
		$transactionsRequiringWarning = Transaction::whereHas('status', function($q) {
			$q->whereIn('code', [500, 501, 502, 503]);
		})->whereHas('notification', function($q) {
			$q->where([
				['fix_bill_sent', true],
				['number_times_fix_bill_sent', '<', 3]
			])->whereTime('time_fix_bill_sent', '<=', now()->subMinutes(10));
		})->get();

		foreach ($transactionsRequiringWarning as $transaction) {
			$transaction->customer->notify(new FixBill($transaction));
		}
	}
}