<?php

namespace App\Observers\Business;

use App\Models\Business\ProfilePhotos;

class ProfilePhotosObserver {

	public function saved(ProfilePhotos $photos) {
		if ($photos->logo_id && $photos->banner_id) {
			if ($photos->profile->business->account->status->code == 101) {
				$photos->profile->business->account->setStatus(102);
			}
		}
	}
}
