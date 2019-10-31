<?php

namespace App\Observers\Transaction;

use App\Models\Transaction\UnassignedTransaction;
use App\Models\Transaction\Transaction;

class UnassignedTransactionObserver {

	public function saved(UnassignedTransaction $unassignedTransaction) {
		if ($this->transactionAssigned($unassignedTransaction)) {
			$unassignedTransaction->delete();
		}
	}

	public function deleting(UnassignedTransaction $unassignedTransaction) {
		if ($this->transactionAssigned($unassignedTransaction)) {
			$transaction = Transaction::createTransaction($unassignedTransaction);
			$transaction->addPurchasedItems($unassignedTransaction);
		}	
	}


	private function transactionAssigned($unassignedTransaction) {
		return isset($unassignedTransaction->customer_id) && !is_null($unassignedTransaction->customer_id);
	}
}
