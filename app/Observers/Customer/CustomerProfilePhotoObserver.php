<?php

namespace App\Observers\Customer;

use App\Models\Customer\CustomerProfilePhoto;

class CustomerProfilePhotoObserver {
	public function saved(CustomerProfilePhoto $photo) {
		if ($photo->profile->customer->status->code == 101) {
			$photo->profile->customer->setStatus(102);
		}
	}
}