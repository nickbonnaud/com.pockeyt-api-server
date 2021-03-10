<?php

namespace App\Models\Refund;

use Illuminate\Database\Eloquent\Model;

class RefundStatus extends Model {
  
  //////////////////// Attribute Mods/Helpers ////////////////////

	protected $fillable = ['name', 'code'];
	protected $hidden = ['id', 'created_at', 'updated_at'];
  protected $casts = ['code' => 'integer'];

  //////////////////// Relationships ////////////////////

  public function refunds() {
  	return $this->hasMany('App\Models\Refund\Refund', 'status_id');
  }

  //////////////////// Core Methods ////////////////////

  public static function getByName($statusName) {
    return self::where('name', $statusName)->first();
  }
}
