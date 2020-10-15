<?php

namespace App\Models\Customer;

use Carbon\Carbon;
use App\Models\Customer\CustomerStatus;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use App\Notifications\Customer\ResetPassword;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Customer extends Authenticatable implements JWTSubject {
  
  //////////////////// Traits ////////////////////

	use Notifiable;
	use \BinaryCabin\LaravelUUID\Traits\HasUUID;

	//////////////////// Attribute Mods/Helpers ////////////////////

	protected $fillable = ['email', 'password'];
	protected $hidden = ['password', 'remember_token', 'email_verified_at', 'id', 'customer_status_id'];
	protected $casts = ['email_verified_at' => 'datetime'];
	protected $uuidFieldName = 'identifier';

	//////////////////// Routing ////////////////////

	public function getRouteKeyName() {
		return 'identifier';
	}

	public function routeNotificationForOneSignal() {
    return ['include_external_user_ids' => [$this->identifier]];
	}

	//////////////////// Relationships ////////////////////

	public function status() {
		return $this->belongsTo('App\Models\Customer\CustomerStatus', 'customer_status_id');
	}

	public function profile() {
		return $this->hasOne('App\Models\Customer\CustomerProfile');
	}

	public function pushToken() {
		return $this->hasOne('App\Models\Customer\PushToken');
	}

	public function account() {
		return $this->hasOne('App\Models\Customer\CustomerAccount');
	}

	public function loyaltyCard() {
		return $this->hasOne('App\Models\Customer\LoyaltyCard');
	}

	public function transactions() {
		return $this->hasMany('App\Models\Transaction\Transaction');
	}

	public function activeLocations() {
		return $this->hasMany('App\Models\Location\ActiveLocation');
	}

	public function historicLocations() {
		return $this->hasMany('App\Models\Location\HistoricLocation');
	}

	public function onStartLocations() {
		return $this->hasMany('App\Models\Location\OnStartLocation');
	}

	public function helpTickets() {
		return $this->hasMany('App\Models\Customer\HelpTicket');
	}

	//////////////////// Relationship Methods ////////////////////

	public function storeProfile($profile) {
		$this->profile()->save($profile);
		return $this->profile;
	}

	public function storePushToken($pushTokenData) {
		$this->pushToken()->updateOrCreate(['customer_id' => $this->id], $pushTokenData);
	}

	//////////////////// JWT Helpers ////////////////////

	public function getJWTIdentifier() {
		return $this->getKey();
	}

	public function getJWTCustomClaims() {
		return [];
	}

	//////////////////// Core Methods ////////////////////

	public static function login($credentials) {
		if (!auth('customer')->validate($credentials)) {
      return ['token' => null, 'error' => 'invalid_credentials', 'code' => 401]; 
    }
    $customer = Customer::where('email', $credentials['email'])->first();
    return self::createToken($customer);
	}

	public static function logout() {
		auth('customer')->logout();
		return ['token' => null, 'error' => null, 'code' => 200];
	}

	public static function refreshToken() {
		return auth('customer')->refresh();
	}

	public static function updateToken() {
		$token = self::refreshToken();
		return ['token' => $token, 'error' => null, 'code' => 200];
	}

	public static function createToken($customer) {
		try {
  		if (!$token = auth('customer')->login($customer))
  			return ['token' => null, 'error' => 'invalid_credentials', 'code' => 401]; 
  	} catch(Exceptions\JWTException $e) {
  		return ['token' => null, 'error' => 'could_not_create_token', 'code' => 500];
  	}
  	return ['token' => $token, 'error' => null, 'code' => 200];
	}

	public static function getAuthCustomer() {
		return auth('customer')->user();
	}

	public function setStatus($code) {
		$this->customer_status_id = CustomerStatus::where('code', $code)->first()->id;
		$this->save();
	}

	//////////////////// Formatting Methods ////////////////////

	public static function formatToken($token = null) {
		return [
      'value' => $token,
      'expiry' => $token ? Carbon::now()->addMinutes(env('JWT_TTL'))->timestamp : null
    ];
	}

	public static function getByIdentifier($identifier) {
		return self::where('identifier', $identifier)->first();
	}

	//////////////////// Inherited Overrides Methods ////////////////////

	public function sendPasswordResetNotification($token){
    $this->notify(new ResetPassword($token));
	}
}
