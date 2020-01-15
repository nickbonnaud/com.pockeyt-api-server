<?php

namespace App\Observers\Business;

use Illuminate\Support\Arr;
use App\Models\Business\PayFacOwner;
use App\Models\Business\AchOwner;

class PayFacOwnerObserver {

	public function creating(PayFacOwner $payFacOwner) {
		AchOwner::storeData($this->scrubAchOwnerData($payFacOwner));
	}

	public function created(PayFacOwner $payFacOwner) {
		if ($payFacOwner->payFacAccount->payFacOwners()->where('primary', true)->exists()) {
			$payFacOwner->payFacAccount->account->setStatus(104);
		}
	}

	public function updating(PayFacOwner $payFacOwner) {
		$owners = $this->getAchAccount($payFacOwner)->achOwners;
		$owners->where('identifier', $payFacOwner->identifier)->first()->updateData($this->scrubAchOwnerData($payFacOwner));
	}




	private function scrubAchOwnerData($payFacOwner) {
		$scrubbedData = Arr::only($payFacOwner->toArray(), [
			'identifier',
			'first_name',
			'last_name',
			'title',
			'email',
			'dob',
			'address',
			'address_secondary',
			'city',
			'state',
			'zip',
			'primary'
		]);
		$scrubbedData['ach_account_id'] = $this->getAchAccount($payFacOwner)->id;
		$scrubbedData['ssn'] = $payFacOwner->ssn;
		return $scrubbedData;
	}

	private function getAchAccount($payFacOwner) {
		return $payFacOwner->payFacAccount->account->achAccount;
	}
}
