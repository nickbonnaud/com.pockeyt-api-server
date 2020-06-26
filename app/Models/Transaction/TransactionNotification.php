<?php

namespace App\Models\Transaction;

use Illuminate\Database\Eloquent\Model;
use App\Models\Transaction\Transaction;

class TransactionNotification extends Model {
  
  //////////////////// Attribute Mods/Helpers ////////////////////

  protected $guarded = [];
  protected $hidden = ['id'];

  protected $casts = [
    'exit_sent' => 'boolean',
    'bill_closed_sent' => 'boolean',
    'auto_pay_sent' => 'boolean',
    'fix_bill_sent' => 'boolean',
  ];

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

  public function addWarningSent() {
    $this->update(['number_times_fix_bill_sent' => $this->number_times_fix_bill_sent + 1]);
  }

  public function resetWarnings() {
    $this->update(['fix_bill_sent' => false, 'time_fix_bill_sent' => null, 'number_times_fix_bill_sent' => 0]);
  }
}
