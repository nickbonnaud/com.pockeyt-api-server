<?php

namespace App\Observers\Admin;

use App\Models\Admin\Admin;
use Illuminate\Support\Facades\Hash;

class AdminObserver {

  public function creating(Admin $admin) {
    $admin->password = Hash::make($admin->password);
  }
}
