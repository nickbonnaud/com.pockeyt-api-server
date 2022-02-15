<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Model;

class ResetCode extends Model {

  //////////////////// Attribute Mods/Helpers ////////////////////

  protected $fillable = ['value'];

  //////////////////// Relationships ////////////////////

  public function customer() {
    return $this->belongsTo('App\Models\Customer\Customer');
  }
}
