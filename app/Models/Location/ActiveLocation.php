<?php

namespace App\Models\Location;

use Illuminate\Database\Eloquent\Model;

class ActiveLocation extends Model {
  
  //////////////////// Traits ////////////////////

	use \BinaryCabin\LaravelUUID\Traits\HasUUID;

  //////////////////// Attribute Mods/Helpers ////////////////////

  protected $guarded = [];
  protected $hidden = ['id', 'created_at', 'updated_at'];
  protected $uuidFieldName = 'identifier';

  //////////////////// Routing ////////////////////

	public function getRouteKeyName() {
		return 'identifier';
	}

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

	public static function createLocation($customer, $location, $locationData) {
		$activeLocation = self::getActiveLocation($customer, $location);
		if ($activeLocation) {
			$activeLocation->touch();
		} else {
			$activeLocation = self::create(['customer_id' => $customer->id, 'location_id' => $location->id]);
		}
		return $activeLocation;
	}

	public static function getActiveLocation($customer, $location) {
		return self::where('customer_id', $customer->id)->where('location_id', $location->id)->first();
	}

	public function updateLocation($locationData) {
		return $this->touch();
	}

	public function destroyLocation($locationData) {
		if ($this->transaction_id && $this->transaction->status->name != 'paid') {
			// send notif
		} else {
			$this->delete();
		}
	}

	public function scopeFilter($query, $filters) {
		return $filters->apply($query);
	}
}
