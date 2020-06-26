<?php

namespace App\Models\Location;

use Illuminate\Database\Eloquent\Model;
use App\Notifications\Customer\EnterBusiness;
use App\Notifications\Customer\FixBill;

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

	public static function createLocation($customer, $location) {
		$activeLocation = self::getActiveLocation($customer, $location);
		if ($activeLocation) {
			if ($activeLocation->transaction_id) {
				if ($activeLocation->transaction->status->code == 105 ||$activeLocation->transaction->status->code == 106) {
					$activeLocation->transaction->updateStatus(100);
				} else if ($activeLocation->transaction->status->code >= 500 && !is_null($activeLocation->transaction->notification)) {
					$activeLocation->transaction->notification->resetWarnings();
				}
			} else {
				$activeLocation->touch();
			}
		} else {
			$activeLocation = self::create(['customer_id' => $customer->id, 'location_id' => $location->id]);
			if (!self::recentlyPreviouslyCreated($customer, $location->business)) {
				$customer->notify(new EnterBusiness($location->business));
			}
		}
		return $activeLocation;
	}

	public static function getActiveLocation($customer, $location) {
		return self::where('customer_id', $customer->id)->where('location_id', $location->id)->first();
	}

	public function destroyLocation() {
		if ($this->transaction_id && ($this->transaction->status->code != 200 && $this->transaction->status->code != 103 && $this->transaction->status->code != 104)) {
			if ($this->transaction->status->code >= 500) {
				$this->customer->notify(new FixBill($this->transaction));
			} elseif ($this->transaction->status->code != 101) {
				$this->transaction->updateStatus(105);
			}
		} else {
			$this->delete();
		}
	}

	public function scopeFilter($query, $filters) {
		return $filters->apply($query);
	}

	private static function recentlyPreviouslyCreated($customer, $business) {
		return $customer->historicLocations()
      ->where('location_id', $business->location->id)
      ->where('created_at', '>=', now()->subDay())
      ->exists();
	}
}
