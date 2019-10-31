<?php

namespace App\Models\Business;

use Illuminate\Database\Eloquent\Model;

class PosAccount extends Model {

	//////////////////// Traits ////////////////////

	use \BinaryCabin\LaravelUUID\Traits\HasUUID;
  
  //////////////////// Attribute Mods/Helpers ////////////////////

  protected $fillable = ['business_id', 'type', 'takes_tips', 'allows_open_tickets'];
  protected $hidden = ['business_id', 'id', 'account_status_id', 'updated_at', 'created_at'];
  protected $uuidFieldName = 'identifier';

  //////////////////// Routing ////////////////////

	public function getRouteKeyName() {
		return 'identifier';
	}

  //////////////////// Relationships ////////////////////

	public function status() {
		return $this->belongsTo('App\Models\Business\PosAccountStatus', 'pos_account_status_id');
	}

	public function business() {
		return $this->belongsTo('App\Models\Business\Business');
	}

	public function squareAccount() {
		return $this->hasOne('App\Models\Business\SquareAccount');
	}

	public function cloverAccount() {
		return $this->hasOne('App\Models\Business\CloverAccount');
	}

	public function lightspeedRetailAccount() {
		return $this->hasOne('App\Models\Business\LightspeedRetailAccount');
	}

	public function shopifyAccount() {
		return $this->hasOne('App\Models\Business\ShopifyAccount');
	}

	public function vendAccount() {
		return $this->hasOne('App\Models\Business\VendAccount');
	}

	 //////////////////// Core Methods ////////////////////

	public static function createAccount($business, $posAccountData) {
		$posAccountData['business_id'] = $business->id;
		return self::create($posAccountData);
	}

	public function closePosBill($transaction) {
		switch ($this->type) {
			case 'clover':
				$this->cloverAccount->closeOrder($transaction);
				break;
			
			default:
				# code...
				break;
		}
	}

	public function createBillIdentifier($customer, $transaction = null) {
		switch ($this->type) {
			case 'square':
				return $this->squareAccount->createCustomer($customer);
				break;
			case 'clover':
				$this->cloverAccount->addCustomerNote($customer, $transaction);
				break;
			case 'vend':
				return $this->vendAccount->createCustomer($customer);
				break;
			default:
				return $this->type .  '_' . $customer->identifier;
				break;
		}
	}

	public function destroyBillIdentifier($billIdentifier) {
		switch ($this->type) {
			case 'square':
				$this->squareAccount->destroyCustomer($billIdentifier);
				break;
			case 'vend':
				$this->vendAccount->destroyCustomer($billIdentifier);
				break;
			default:
				# code...
				break;
		}
	}

	public function getPosAccount() {
		switch ($this->type) {
			case 'square':
				return $this->squareAccount;
				break;
			case 'clover':
				return $this->cloverAccount;
				break;
			case 'lightspeed_retail':
				return $this->lightspeedRetailAccount;
				break;
			case 'shopify':
				return $this->shopifyAccount;
				break;
			case 'vend':
				return $this->vendAccount;
				break;
			default:
				# code...
				break;
		}
	}
}
