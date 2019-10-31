<?php

namespace App\Observers\Location;

use App\Models\Location\ActiveLocation;
use App\Models\Location\HistoricLocation;

class ActiveLocationObserver {

	public function creating(ActiveLocation $activeLocation) {
		$billIdentifier = $this->createBillIdentifier($activeLocation);
		$activeLocation->bill_identifier = $billIdentifier;
	}

	public function deleting(ActiveLocation $activeLocation) {
		$this->destroyBillIdentifier($activeLocation);
		$this->moveToHistoric($activeLocation);
	}

	

	private function createBillIdentifier($activeLocation) {
		return $activeLocation->location->business->posAccount->createBillIdentifier($activeLocation->customer);
	}

	private function destroyBillIdentifier($activeLocation) {
		$activeLocation->location->business->posAccount->destroyBillIdentifier($activeLocation->bill_identifier);
	}

	private function moveToHistoric($activeLocation) {
		HistoricLocation::createHistoricLocation($activeLocation->fresh()->toArray());
	}
}
