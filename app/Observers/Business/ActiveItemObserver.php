<?php

namespace App\Observers\Business;

use App\Models\Business\ActiveItem;
use App\Models\Business\InactiveItem;

class ActiveItemObserver {

	public function deleting(ActiveItem $activeItem) {
		InactiveItem::createInactiveItem($activeItem);
	}
}
