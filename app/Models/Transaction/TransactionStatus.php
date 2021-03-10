<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;

class TransactionStatus extends Model {
  
  //////////////////// Attribute Mods/Helpers ////////////////////

	protected $fillable = ['name', 'code'];
	protected $hidden = ['id', 'created_at', 'updated_at'];
  protected $casts = ['code' => 'integer'];

  //////////////////// Relationships ////////////////////

  public function transactions() {
  	return $this->hasMany('App\Models\Transaction\Transaction', 'status_id');
  }

  //////////////////// Core Methods ////////////////////

  public static function getByName($statusName) {
    return self::where('name', $statusName)->first();
  }
}
