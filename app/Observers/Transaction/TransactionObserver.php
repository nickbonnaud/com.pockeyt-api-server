<?php

namespace App\Observers\Transaction;

use App\Models\Transaction\Transaction;
use App\Notifications\Customer\BillClosed;
use App\Models\Transaction\TransactionStatus;

class TransactionObserver {

	public function creating(Transaction $transaction) {
		if (!$transaction->status_id) {
			$transaction->status_id = (TransactionStatus::where('code', 100)->first())->id;
		}
	}

	public function saved(Transaction $transaction) {
		if (isset($transaction->business->posAccount) && $transaction->business->posAccount->type == 'clover') {
			$this->updateCloverTransaction($transaction);
		}

		if ($transaction->employee_id && !$transaction->business->employees()->where('external_id', $transaction->employee_id)->exists()) {
			if ($transaction->status->code == 200) {
				$transaction->business->posAccount->getPosAccount()->createEmployee($transaction->employee_id);
			}
		}
	}


	private function updateCloverTransaction($transaction) {
		if ($transaction->getOriginal('customer_id') != $transaction->customer_id) {
			$transaction->business->posAccount->createBillIdentifier($transaction->fresh()->customer, $transaction);
		}
	}
}
