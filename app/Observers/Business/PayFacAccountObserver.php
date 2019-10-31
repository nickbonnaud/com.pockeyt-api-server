<?php

namespace App\Observers\Business;

use App\Models\Business\PayFacAccount;
use App\Models\Business\AchAccount;

class PayFacAccountObserver {

	public function creating(PayFacAccount $payFacAccount) {
		AchAccount::createAchAccount($payFacAccount);
		$payFacAccount->entity_type = $payFacAccount->entity_type == 'soleProprietorship' ? 'Individual' : 'Business';
	}

	public function updating(PayFacAccount $payFacAccount) {
		$payFacAccount->account->achAccount->updateAchData($payFacAccount);
		$payFacAccount->entity_type = $payFacAccount->entity_type == 'soleProprietorship' ? 'Individual' : 'Business';
	}
}
