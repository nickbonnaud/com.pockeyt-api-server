<?php

namespace App\Models\Location;

use Illuminate\Database\Eloquent\Model;

class HistoricLocation extends Model {
  
  //////////////////// Attribute Mods/Helpers ////////////////////

  protected $guarded = [];
  protected $visible = [];

  //////////////////// Relationships ////////////////////

  public function customer() {
		return $this->belongsTo('App\Models\Customer\Customer');
	}

	public function location() {
		return $this->belongsTo('App\Models\Business\Location');
	}

	public function notification() {
		return $this->belongsTo('App\Models\Transaction\TransactionNotification', 'transaction_notification_id');
	}

	public function transaction() {
		return $this->belongsTo('App\Models\Transaction\Transaction');
	}

	//////////////////// Core Methods ////////////////////

	public static function createHistoricLocation($locationData) {
		self::create($locationData);
	}

	public function scopeFilter($query, $filters) {
		return $filters->apply($query);
	}
}
