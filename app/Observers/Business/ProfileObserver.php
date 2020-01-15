<?php

namespace App\Observers\Business;

use App\Models\Business\Profile;
use App\Models\Business\ProfilePhotos;


class ProfileObserver
{
  public function created(Profile $profile) {
    $profile->photos()->save(new ProfilePhotos);
    $profile->business->account->setStatus(101);
  }
}
