<?php

namespace App\Observers\Business;

use Illuminate\Support\Arr;
use App\Models\Business\PayFacBusiness;
use App\Models\Business\AchBusiness;
use App\Models\Business\Location;

class PayFacBusinessObserver {

	public function creating(PayFacBusiness $payFacBusiness) {
		AchBusiness::storeData($this->scrubAchBusinessData($payFacBusiness));
	}

	public function updating(PayFacBusiness $payFacBusiness) {
		$this->getAchAccount($payFacBusiness)->achBusiness->updateData($this->scrubAchBusinessData($payFacBusiness));
	}

	





	private function scrubAchBusinessData($payFacBusiness) {
		$scrubbedData = Arr::only($payFacBusiness->toArray(), [
			'identifier',
			'business_name',
			'address',
			'address_secondary',
			'city',
			'state',
			'zip',
			'business_classification',
			'ein'
		]);
		$scrubbedData['ach_account_id'] = $this->getAchAccount($payFacBusiness)->id;
		return $scrubbedData;
	}

	private function getAchAccount($payFacBusiness) {
		return $payFacBusiness->payFacAccount->account->achAccount;
	}
}
