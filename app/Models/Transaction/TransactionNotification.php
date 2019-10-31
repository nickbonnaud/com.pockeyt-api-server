<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Models\Transaction\Transaction;

class TransactionNotification extends Model {
  
  //////////////////// Attribute Mods/Helpers ////////////////////

  protected $guarded = [];
  protected $hidden = ['id'];

  //////////////////// Relationships ////////////////////

  public function transaction() {
  	return $this->belongsTo('App\Models\Transaction\Transaction');
  }

  public function activeCustomerLocation() {
  	return $this->hasOne('App\Models\Location\ActiveLocation');
  }

  public function historicCustomerLocation() {
  	return $this->hasOne('App\Models\Location\HistoricLocation');
  }

  //////////////////// Core Methods ////////////////////

  public static function storeNewNotification($transactionIdentifier, $type) {
    $name = $type . '_sent';
    $timeName = 'time_' . $name;

    $notification = self::updateOrCreate([
      'transaction_id' => Transaction::where('identifier', $transactionIdentifier)->first()->id
    ],
    [
      'last' => $type,
      $name => true,
      $timeName => now(),
    ]);
  }
}
