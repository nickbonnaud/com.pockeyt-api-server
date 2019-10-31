<?php

namespace App\Observers\Transaction;

use App\Models\Transaction\Transaction;
use App\Notifications\Customer\BillClosed;
use App\Models\Transaction\TransactionStatus;

class TransactionObserver {

	public function creating(Transaction $transaction) {
		if (!$transaction->status_id) {
			$transaction->status_id = (TransactionStatus::where('name', 'open')->first())->id;
		}
	}

	public function saved(Transaction $transaction) {
		if ($transaction->status->name == 'closed') {
			$transaction->customer->notify(new BillClosed($transaction));
		} elseif ($transaction->status->name == 'paid') {
			$transaction->business->posAccount->closePosBill($transaction);
		}

		
		if ($transaction->business->posAccount->type == 'clover') {
			$this->updateCloverTransaction($transaction);
		}

		if ($transaction->employee_id && !$transaction->business->employees()->where('external_id', $transaction->employee_id)->exists()) {
			if ($transaction->status->name == 'paid') {
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
